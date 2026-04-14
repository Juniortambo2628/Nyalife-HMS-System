<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMedicineRequest;
use App\Http\Requests\UpdateMedicineRequest;
use App\Http\Requests\UpdatePharmacyStockRequest;
use App\Models\Medication;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PharmacyController extends Controller
{
    public function inventory(Request $request)
    {
        $inventory = Medication::query()
            ->searchByNameOrType($request->query('search'))
            ->latest()
            ->paginate(15);

        return Inertia::render('Pharmacy/Inventory', [
            'inventory' => $inventory,
            'filters' => $request->only('search')
        ]);
    }

    public function medicines(Request $request)
    {
        $medicines = Medication::query()
            ->searchByNameOrType($request->query('search'))
            ->latest()
            ->paginate(20);

        return Inertia::render('Pharmacy/Medicines', [
            'medicines' => $medicines,
            'filters' => $request->only('search')
        ]);
    }

    /**
     * Store a new medicine in the catalog.
     */
    public function storeMedicine(StoreMedicineRequest $request)
    {
        $validated = $request->validated();
        $medication = Medication::create($validated + ['stock_quantity' => 0]);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'medication_id' => $medication->medication_id,
                'message' => 'Medicine added to catalog successfully.'
            ]);
        }

        return redirect()->back()->with('success', 'Medicine added to catalog successfully.');
    }

    /**
     * Update an existing medicine.
     */
    public function updateMedicine(UpdateMedicineRequest $request, $id)
    {
        $medication = Medication::findOrFail($id);
        $validated = $request->validated();
        $medication->update($validated);

        return redirect()->back()->with('success', 'Medicine updated successfully.');
    }

    /**
     * Remove a medicine from the catalog.
     */
    public function destroyMedicine($id)
    {
        $medication = Medication::findOrFail($id);
        $medication->delete();

        return redirect()->back()->with('success', 'Medicine removed from catalog.');
    }

    /**
     * Update stock quantity for a medication.
     */
    public function updateStock(UpdatePharmacyStockRequest $request)
    {
        $validated = $request->validated();
        $medication = Medication::findOrFail($validated['medication_id']);

        if ($validated['type'] === 'add') {
            $medication->increment('stock_quantity', $validated['quantity']);
        } else {
            $medication->update(['stock_quantity' => $validated['quantity']]);
        }

        return redirect()->back()->with('success', 'Inventory updated successfully.');
    }

    /**
     * Search medications via AJAX.
     */
    public function searchAjax(Request $request)
    {
        $medications = Medication::query()
            ->searchByNameOrType($request->query('q'))
            ->limit(20)
            ->get()
            ->map(function ($med) {
                return [
                    'value' => $med->medication_id,
                    'label' => "{$med->medication_name} ({$med->strength} {$med->unit})"
                ];
            });

        return response()->json($medications);
    }
}
