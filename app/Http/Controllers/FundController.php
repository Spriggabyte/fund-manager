<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreFundRequest;
use App\Http\Requests\UpdateFundRequest;
use App\Models\Fund;
use App\Models\FundRevision;
use App\Services\ExcelImportService;
use App\Services\PuppeteerPdfService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class FundController extends Controller
{
    use AuthorizesRequests;

    public function index(): View
    {
        $funds = auth()->user()->funds()->latest()->get();

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

    public function edit(Fund $fund): View
    {
        $this->authorize('update', $fund);

        return view('funds.edit', compact('fund'));
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
     * Import data from Excel files.
     */
    public function import(Request $request, Fund $fund)
    {
        $this->authorize('update', $fund);

        $request->validate([
            'factsheet' => 'nullable|file|mimes:xlsx,xls',
            'price_graph' => 'nullable|file|mimes:xlsx,xls',
            'inflation_graph' => 'nullable|file|mimes:xlsx,xls',
        ]);

        if (! $request->hasFile('factsheet') && ! $request->hasFile('price_graph') && ! $request->hasFile('inflation_graph')) {
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

        $fund->save();

        return redirect()->route('funds.edit', $fund)
            ->with('success', 'Imported '.implode(' and ', $imported).' data successfully.');
    }

    public function exportPdf(Fund $fund, PuppeteerPdfService $puppeteerService)
    {
        $this->authorize('view', $fund);

        set_time_limit(180);
        ini_set('max_execution_time', 180);

        try {
            $pdfPath = $puppeteerService->generatePdf($fund);
            $filename = 'fund-'.$fund->id.'-'.now()->format('Y-m-d').'.pdf';

            return response()->download($pdfPath, $filename)->deleteFileAfterSend(true);

        } catch (\Exception $e) {
            Log::error('PDF generation failed: '.$e->getMessage());
            Log::error('PDF error trace: '.$e->getTraceAsString());

            return response()->json([
                'error' => 'PDF generation failed',
                'message' => 'Unable to generate PDF. Please try again or contact support.',
                'details' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
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
        $pdfTemplate = $template === 'show-equity' ? 'pdf-equity' : 'pdf';
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
