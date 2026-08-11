<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreFundRequest;
use App\Http\Requests\UpdateFundRequest;
use App\Jobs\GenerateFundPdfJob;
use App\Models\Fund;
use App\Models\FundPdfExport;
use App\Models\FundRevision;
use App\Services\ExcelImportService;
use App\Services\FundImport\FundDataSyncService;
use App\Services\FundImport\FundImportManager;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class FundController extends Controller
{
    use AuthorizesRequests;

    public function index(): View
    {
        // Shared workspace: everyone sees every fund. auth()->user()->funds()
        // only records authorship — see FundPolicy.
        $funds = Fund::latest()->get();

        return view('funds.index', compact('funds'));
    }

    public function create(): View
    {
        return view('funds.create');
    }

    public function store(StoreFundRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $validated = $this->decodeJsonFields($validated);

        auth()->user()->funds()->create($validated);

        return redirect()->route('funds.index')
            ->with('success', 'Fund created successfully.');
    }

    private const ALLOWED_TEMPLATES = ['show', 'show-equity', 'show-flexible', 'show-international'];

    public function show(Fund $fund): View
    {
        $this->authorize('view', $fund);

        $template = $fund->template ?? 'show';
        if (! in_array($template, self::ALLOWED_TEMPLATES)) {
            $template = 'show';
        }

        return view('funds.'.$template, compact('fund'));
    }

    public function factSheet(Fund $fund): View
    {
        $this->authorize('view', $fund);

        return view('funds.fact-sheet', compact('fund'));
    }

    public function edit(Fund $fund, FundDataSyncService $syncService): View
    {
        $this->authorize('update', $fund);

        $availableMonths = $syncService->availableMonths($fund->fund_code, $fund->class_code);

        return view('funds.edit', compact('fund', 'availableMonths'));
    }

    public function update(UpdateFundRequest $request, Fund $fund): RedirectResponse
    {
        $this->authorize('update', $fund);

        $validated = $request->validated();
        $validated = $this->decodeJsonFields($validated);

        $fund->update($validated);

        return redirect()->route('funds.show', $fund)
            ->with('success', 'Fund updated successfully.');
    }

    public function destroy(Fund $fund): RedirectResponse
    {
        $this->authorize('delete', $fund);

        $fund->delete();

        return redirect()->route('funds.index')
            ->with('success', 'Fund deleted successfully.');
    }

    /**
     * Update a specific field in the fund's data via inline editing.
     */
    public function updateData(Request $request, Fund $fund)
    {
        $this->authorize('update', $fund);

        $validated = $request->validate([
            'field' => 'required|string',
            'value' => 'nullable',
        ]);

        $fieldPath = $validated['field'];
        $value = $validated['value'];

        $oldValue = $fund->getDataValue($fieldPath);

        // Create revision before updating
        $fund->createRevision(
            $fieldPath,
            $oldValue,
            $value,
            "Updated {$fieldPath}"
        );

        $fund->setDataValueByPath($fieldPath, $value);
        $fund->save();

        return response()->json([
            'success' => true,
            'message' => 'Fund data updated successfully.',
            'fund_data' => $fund->fresh()->data,
        ]);
    }

    /**
     * Update a specific holding in the fund's holdings array.
     */
    public function updateHolding(Request $request, Fund $fund)
    {
        $this->authorize('update', $fund);

        $validated = $request->validate([
            'index' => 'required|integer|min:0',
            'holding' => 'required|array',
            'holding.symbol' => 'required|string',
            'holding.percentage' => 'required|numeric|min:0|max:100',
        ]);

        $topInvestments = $fund->top_investments ?? [];
        $rows = $topInvestments['rows'] ?? [];

        if (isset($rows[$validated['index']])) {
            $rows[$validated['index']] = array_merge(
                $rows[$validated['index']],
                $validated['holding']
            );

            $topInvestments['rows'] = $rows;
            $fund->update(['top_investments' => $topInvestments]);

            return response()->json([
                'success' => true,
                'message' => 'Holding updated successfully.',
                'fund_data' => $fund->fresh()->data,
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Holding not found.',
        ], 404);
    }

    /**
     * Import a downloaded data-feed month (storage/app/private/fund-data)
     * into the fund, snapshotting a revision first.
     */
    public function importMonth(Fund $fund, string $month, FundImportManager $manager): RedirectResponse
    {
        $this->authorize('update', $fund);

        if (! $fund->fund_code) {
            return redirect()->route('funds.edit', $fund)
                ->with('error', 'Set this fund\'s Fund Code before importing from the data feed.');
        }

        $directory = FundDataSyncService::LOCAL_ROOT."/{$month}/{$fund->fund_code}";
        $disk = Storage::disk('local');

        if (! $disk->exists($directory)) {
            return redirect()->route('funds.edit', $fund)
                ->with('error', "No downloaded data for {$month} (fund code {$fund->fund_code}). The daily sync may not have picked it up yet.");
        }

        $result = $manager->importDirectoryWithSnapshot(
            $fund,
            $disk->path($directory),
            "Before data feed import ({$month})"
        );

        if (! $result['imported']) {
            return redirect()->route('funds.edit', $fund)
                ->with('error', "No recognised export files found in the {$month} download.");
        }

        $summary = 'Imported '.implode(', ', array_unique($result['imported'])).' from the '.$month.' data feed.';
        if ($result['skipped']) {
            $summary .= ' Skipped (no importer): '.implode(', ', $result['skipped']).'.';
        }

        return redirect()->route('funds.edit', $fund)->with('success', $summary);
    }

    /**
     * Import data from Excel files.
     */
    public function import(Request $request, Fund $fund)
    {
        $this->authorize('update', $fund);

        $request->validate([
            'factsheet' => 'nullable|file|mimes:xlsx,xls',
            'price_graph' => 'nullable|file|mimes:xlsx,xls',
            'inflation_graph' => 'nullable|file|mimes:xlsx,xls',
            'alsi_graph' => 'nullable|file|mimes:xlsx,xls',
        ]);

        if (! $request->hasFile('factsheet') && ! $request->hasFile('price_graph')
            && ! $request->hasFile('inflation_graph') && ! $request->hasFile('alsi_graph')) {
            return redirect()->route('funds.edit', $fund)
                ->with('error', 'Please upload at least one Excel file.');
        }

        // Create revision before import
        $fund->createRevision(null, null, null, 'Before Excel import');

        $service = new ExcelImportService;
        $imported = [];

        if ($request->hasFile('factsheet')) {
            $service->importFactsheet($fund, $request->file('factsheet')->getRealPath());
            $imported[] = 'factsheet';
        }

        if ($request->hasFile('price_graph')) {
            $service->importPriceGraph($fund, $request->file('price_graph')->getRealPath());
            $imported[] = 'price graph';
        }

        if ($request->hasFile('inflation_graph')) {
            $service->importInflationGraph($fund, $request->file('inflation_graph')->getRealPath());
            $imported[] = 'inflation graph';
        }

        if ($request->hasFile('alsi_graph')) {
            $service->importAlsiGraph($fund, $request->file('alsi_graph')->getRealPath());
            $imported[] = 'ALSI graph';
        }

        $fund->save();

        return redirect()->route('funds.edit', $fund)
            ->with('success', 'Imported '.implode(' and ', $imported).' data successfully.');
    }

    /**
     * Queue a fact-sheet PDF render and show a page that polls for completion.
     * PDF generation is heavy (headless Chrome), so it must not block a web
     * worker — it runs on the queue and the browser downloads it when ready.
     */
    public function exportPdf(Fund $fund): View
    {
        $this->authorize('view', $fund);

        $export = FundPdfExport::create([
            'fund_id' => $fund->id,
            'user_id' => auth()->id(),
            'template' => $fund->template ?? 'show',
            'status' => FundPdfExport::STATUS_PENDING,
        ]);

        GenerateFundPdfJob::dispatch($export);

        return view('funds.pdf-preparing', compact('fund', 'export'));
    }

    /**
     * Report the status of an async PDF export (polled by the preparing page).
     */
    public function exportStatus(FundPdfExport $export)
    {
        $this->authorize('view', $export);

        return response()->json([
            'status' => $export->status,
            'download_url' => $export->isDone()
                ? route('funds.pdf.download', $export)
                : null,
            'error' => $export->isFailed()
                ? 'Unable to generate the PDF. Please try again or contact support.'
                : null,
        ]);
    }

    /**
     * Stream a finished PDF export to the owner.
     */
    public function downloadPdf(FundPdfExport $export)
    {
        $this->authorize('download', $export);

        abort_unless($export->isDone() && $export->path, 404);

        $filename = 'fund-'.$export->fund_id.'-'.$export->created_at->format('Y-m-d').'.pdf';

        return Storage::disk($export->disk)->download($export->path, $filename);
    }

    public function revisions(Fund $fund): View
    {
        $this->authorize('view', $fund);

        $revisions = $fund->revisions()->with('user')->paginate(20);

        return view('funds.revisions', compact('fund', 'revisions'));
    }

    public function restoreRevision(Fund $fund, FundRevision $revision)
    {
        $this->authorize('update', $fund);

        if ($revision->fund_id !== $fund->id) {
            abort(404);
        }

        // Create a revision of the current state before restoring
        $fund->createRevision(
            null,
            null,
            null,
            'Restored to revision from '.$revision->created_at->format('Y-m-d H:i:s')
        );

        // Restore from revision snapshot
        $fund->name = $revision->name;
        $fund->class = $revision->class;
        $fund->restoreFromData($revision->data);
        $fund->save();

        return redirect()->route('funds.show', $fund)
            ->with('success', 'Fund restored to revision from '.$revision->created_at->format('Y-m-d H:i:s'));
    }

    public function showRevision(Fund $fund, FundRevision $revision): View
    {
        $this->authorize('view', $fund);

        if ($revision->fund_id !== $fund->id) {
            abort(404);
        }

        // Create a temporary fund object with revision data for display
        $revisionFund = new Fund;
        $revisionFund->id = $fund->id;
        $revisionFund->name = $revision->name;
        $revisionFund->class = $revision->class;
        $revisionFund->created_at = $fund->created_at;
        $revisionFund->updated_at = $revision->created_at;
        $revisionFund->restoreFromData($revision->data);

        return view('funds.revision-show', compact('fund', 'revision', 'revisionFund'));
    }

    public function internalPdfView(Fund $fund): View
    {
        $template = $fund->template ?? 'show';
        $pdfTemplate = match ($template) {
            'show-equity' => 'pdf-equity',
            'show-flexible' => 'pdf-flexible',
            // The international page template is itself the print layout
            // (A4 pages, @media print rules, .no-print chrome).
            'show-international' => 'show-international',
            default => 'pdf',
        };
        if (! view()->exists('funds.'.$pdfTemplate)) {
            $pdfTemplate = 'pdf';
        }

        return view('funds.'.$pdfTemplate, compact('fund'));
    }

    /**
     * Decode JSON string fields into arrays for storage.
     */
    private function decodeJsonFields(array $data): array
    {
        $jsonFields = [
            'important_info_paragraphs',
            'asset_allocation',
            'top_investments',
            'performance_table',
            'chart_data',
            'sector_allocation',
            'fees',
        ];

        foreach ($jsonFields as $field) {
            if (isset($data[$field]) && is_string($data[$field])) {
                $data[$field] = json_decode($data[$field], true);
            }
        }

        return $data;
    }
}
