<?php

namespace App\Http\Controllers;

use App\Models\Fund;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Barryvdh\DomPDF\Facade\Pdf;

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
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'class' => 'nullable|string|max:255',
            'data' => 'nullable|json',
        ]);

        // Parse JSON data if provided
        if ($validated['data']) {
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
    public function update(Request $request, Fund $fund): RedirectResponse
    {
        $this->authorize('update', $fund);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'class' => 'nullable|string|max:255',
            'data' => 'nullable|json',
        ]);

        // Parse JSON data if provided
        if ($validated['data']) {
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
        
        // Handle nested field paths (e.g., "fund.name", "sidebar.domicile")
        $this->setNestedValue($data, $fieldPath, $value);

        $fund->update(['data' => $data]);

        return response()->json([
            'success' => true,
            'message' => 'Fund data updated successfully.',
            'fund_data' => $fund->fresh()->data
        ]);
    }

    /**
     * Set a nested value in an array using dot notation.
     */
    private function setNestedValue(&$array, $path, $value)
    {
        $keys = explode('.', $path);
        $current = &$array;

        foreach ($keys as $i => $key) {
            if ($i === count($keys) - 1) {
                // Last key, set the value
                if ($value === '' || $value === null) {
                    unset($current[$key]);
                } else {
                    // Handle numeric values
                    if (is_numeric($value) && !in_array($key, ['phone', 'email', 'website', 'date', 'name', 'description'])) {
                        $current[$key] = is_float($value + 0) ? (float) $value : (int) $value;
                    } else {
                        $current[$key] = $value;
                    }
                }
            } else {
                // Create nested array if it doesn't exist
                if (!isset($current[$key]) || !is_array($current[$key])) {
                    $current[$key] = [];
                }
                $current = &$current[$key];
            }
        }
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
                'fund_data' => $fund->fresh()->data
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Holding not found.'
        ], 404);
    }

    /**
     * Export the fund data as a PDF.
     */
    public function exportPdf(Fund $fund)
    {
        $this->authorize('view', $fund);
        
        // Create a clean PDF view without edit controls
        $pdf = Pdf::loadView('funds.pdf', compact('fund'))
            ->setPaper('a4', 'portrait')
            ->setOptions([
                'defaultFont' => 'Arial',
                'isRemoteEnabled' => true,
                'isHtml5ParserEnabled' => true,
                'dpi' => 150,
                'defaultPaperSize' => 'a4',
                'chroot' => public_path(),
            ]);

        $filename = 'fund-' . $fund->id . '-' . now()->format('Y-m-d') . '.pdf';
        
        return $pdf->download($filename);
    }
}
