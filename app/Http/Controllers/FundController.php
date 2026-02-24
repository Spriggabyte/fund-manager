<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreFundRequest;
use App\Http\Requests\UpdateFundRequest;
use App\Models\Fund;
use App\Models\FundRevision;
use App\Services\PuppeteerPdfService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class FundController extends Controller
{
    use AuthorizesRequests;

    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $funds = auth()->user()->funds()->latest()->get();

        return view('funds.index', compact('funds'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('funds.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreFundRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        if ($validated['data'] ?? null) {
            $validated['data'] = json_decode($validated['data'], true);
        }

        auth()->user()->funds()->create($validated);

        return redirect()->route('funds.index')
            ->with('success', 'Fund created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Fund $fund): View
    {
        $this->authorize('view', $fund);

        return view('funds.show', compact('fund'));
    }

    /**
     * Display the fund fact sheet.
     */
    public function factSheet(Fund $fund): View
    {
        $this->authorize('view', $fund);

        return view('funds.fact-sheet', compact('fund'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Fund $fund): View
    {
        $this->authorize('update', $fund);

        return view('funds.edit', compact('fund'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateFundRequest $request, Fund $fund): RedirectResponse
    {
        $this->authorize('update', $fund);

        $validated = $request->validated();

        if ($validated['data'] ?? null) {
            $validated['data'] = json_decode($validated['data'], true);
        }

        $fund->update($validated);

        return redirect()->route('funds.index')
            ->with('success', 'Fund updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Fund $fund): RedirectResponse
    {
        $this->authorize('delete', $fund);

        $fund->delete();

        return redirect()->route('funds.index')
            ->with('success', 'Fund deleted successfully.');
    }

    /**
     * Update a specific field in the fund's JSON data.
     */
    public function updateData(Request $request, Fund $fund)
    {
        $this->authorize('update', $fund);

        $validated = $request->validate([
            'field' => 'required|string',
            'value' => 'nullable',
        ]);

        $data = $fund->data ?? [];
        $fieldPath = $validated['field'];
        $value = $validated['value'];

        $oldValue = $fund->getDataValue($fieldPath);

        $fund->setDataValue($data, $fieldPath, $value);

        // Create revision before updating
        $fund->createRevision(
            $fieldPath,
            $oldValue,
            $value,
            "Updated {$fieldPath}"
        );

        $fund->update(['data' => $data]);

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

        $data = $fund->data ?? [];
        $holdings = $data['holdings'] ?? [];

        if (isset($holdings[$validated['index']])) {
            $holdings[$validated['index']] = array_merge(
                $holdings[$validated['index']],
                $validated['holding']
            );

            $data['holdings'] = $holdings;
            $fund->update(['data' => $data]);

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
     * Export the fund data as a PDF using Puppeteer for pixel-perfect rendering.
     */
    public function exportPdf(Fund $fund, PuppeteerPdfService $puppeteerService)
    {
        $this->authorize('view', $fund);

        set_time_limit(180);
        ini_set('max_execution_time', 180);

        try {
            // Generate PDF using Puppeteer
            $pdfPath = $puppeteerService->generatePdf($fund);

            // Create filename for download
            $filename = 'fund-'.$fund->id.'-'.now()->format('Y-m-d').'.pdf';

            // Return the PDF as download and clean up
            return response()->download($pdfPath, $filename)->deleteFileAfterSend(true);

        } catch (\Exception $e) {
            Log::error('PDF generation failed: '.$e->getMessage());
            Log::error('PDF error trace: '.$e->getTraceAsString());

            // Return error response instead of fallback
            return response()->json([
                'error' => 'PDF generation failed',
                'message' => 'Unable to generate PDF. Please try again or contact support.',
                'details' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Display all revisions for a fund.
     */
    public function revisions(Fund $fund): View
    {
        $this->authorize('view', $fund);

        $revisions = $fund->revisions()->with('user')->paginate(20);

        return view('funds.revisions', compact('fund', 'revisions'));
    }

    /**
     * Restore a fund to a specific revision.
     */
    public function restoreRevision(Fund $fund, FundRevision $revision)
    {
        $this->authorize('update', $fund);

        // Ensure the revision belongs to this fund
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

        // Restore the fund to the revision state
        $fund->update([
            'name' => $revision->name,
            'class' => $revision->class,
            'data' => $revision->data,
        ]);

        return redirect()->route('funds.show', $fund)
            ->with('success', 'Fund restored to revision from '.$revision->created_at->format('Y-m-d H:i:s'));
    }

    /**
     * Show a specific revision.
     */
    public function showRevision(Fund $fund, FundRevision $revision): View
    {
        $this->authorize('view', $fund);

        // Ensure the revision belongs to this fund
        if ($revision->fund_id !== $fund->id) {
            abort(404);
        }

        // Create a temporary fund object with revision data for display
        $revisionFund = new Fund([
            'id' => $fund->id,
            'name' => $revision->name,
            'class' => $revision->class,
            'data' => $revision->data,
            'created_at' => $fund->created_at,
            'updated_at' => $revision->created_at,
        ]);

        return view('funds.revision-show', compact('fund', 'revision', 'revisionFund'));
    }

    /**
     * Internal PDF view - bypasses authentication for Puppeteer access.
     */
    public function internalPdfView(Fund $fund): View
    {
        // No authorization check - this is for internal PDF generation only
        // Render the dedicated PDF template with proper A4 layout
        return view('funds.pdf', compact('fund'));
    }
}
