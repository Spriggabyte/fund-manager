<?php

namespace App\Http\Controllers;

use App\Models\Fund;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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
        
        // Handle different data types appropriately
        $value = $validated['value'];
        if ($validated['field'] === 'value' || $validated['field'] === 'expense_ratio' || $validated['field'] === 'minimum_investment') {
            $value = $value !== null ? (float) $value : null;
        }

        if ($value === '' || $value === null) {
            unset($data[$validated['field']]);
        } else {
            $data[$validated['field']] = $value;
        }

        $fund->update(['data' => $data]);

        return response()->json([
            'success' => true,
            'message' => 'Fund data updated successfully.',
            'fund_data' => $fund->fresh()->data
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
                'fund_data' => $fund->fresh()->data
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Holding not found.'
        ], 404);
    }
}
