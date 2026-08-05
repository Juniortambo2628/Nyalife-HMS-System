<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMedicineRequest;
use App\Http\Requests\UpdateMedicineRequest;
use App\Http\Requests\UpdatePharmacyStockRequest;
use App\Models\Medication;
use App\Models\PharmacyPurchaseOrder;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PharmacyController extends Controller
{
    public function inventory(Request $request)
    {
        $query = Medication::query()
            ->searchByNameOrType($request->query('search'));

        if ($request->has('quick_filter') && $request->quick_filter) {
            switch ($request->quick_filter) {
                case 'low_stock':
                    $query->where('stock_quantity', '>', 0)->where('stock_quantity', '<=', 20);
                    break;
                case 'out_of_stock':
                    $query->where('stock_quantity', 0);
                    break;
            }
        }

        $inventory = $query->latest()
            ->paginate(15);

        return Inertia::render('Pharmacy/Inventory', [
            'inventory' => $inventory,
            'filters' => $request->only(['search', 'quick_filter']),
        ]);
    }

    public function medicines(Request $request)
    {
        $query = Medication::query()
            ->searchByNameOrType($request->query('search'));

        if ($request->has('quick_filter') && $request->quick_filter) {
            $query->where('medication_type', $request->quick_filter);
        }

        $medicines = $query->latest()
            ->paginate(20);

        return Inertia::render('Pharmacy/Medicines', [
            'medicines' => $medicines,
            'filters' => $request->only(['search', 'quick_filter']),
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
                'message' => 'Medicine added to catalog successfully.',
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

        $updateData = [];
        if ($validated['type'] === 'add') {
            $medication->increment('stock_quantity', $validated['quantity']);
        } else {
            $updateData['stock_quantity'] = $validated['quantity'];
        }

        if (array_key_exists('expiry_date', $validated)) {
            $updateData['expiry_date'] = $validated['expiry_date'];
        }

        if (! empty($updateData)) {
            $medication->update($updateData);
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
                    'label' => "{$med->medication_name} ({$med->strength} {$med->unit})",
                ];
            });

        return response()->json($medications);
    }

    /**
     * Display a listing of pharmacy purchase orders.
     */
    public function poIndex(Request $request)
    {
        $orders = PharmacyPurchaseOrder::with('medication')->latest()->paginate(15);
        $lowStockMedications = Medication::where('stock_quantity', '<=', 20)->get();

        return Inertia::render('Pharmacy/PurchaseOrders', [
            'orders' => $orders,
            'lowStockMedications' => $lowStockMedications,
        ]);
    }

    /**
     * Store a new pharmacy purchase order.
     */
    public function storePO(Request $request)
    {
        $validated = $request->validate([
            'medication_id' => 'required|exists:medications,medication_id',
            'quantity' => 'required|integer|min:1',
            'supplier_name' => 'required|string|max:255',
            'estimated_cost' => 'required|numeric|min:0',
        ]);

        $medication = Medication::findOrFail($validated['medication_id']);

        PharmacyPurchaseOrder::create([
            'order_number' => 'PO-'.strtoupper(uniqid()),
            'medication_id' => $medication->medication_id,
            'medication_name' => $medication->medication_name,
            'quantity' => $validated['quantity'],
            'supplier_name' => $validated['supplier_name'],
            'estimated_cost' => $validated['estimated_cost'],
            'status' => 'pending',
        ]);

        return redirect()->back()->with('success', 'Purchase order created successfully.');
    }

    /**
     * Update the status of a pharmacy purchase order.
     */
    public function updatePOStatus(Request $request, $id)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,ordered,received,cancelled',
        ]);

        $order = PharmacyPurchaseOrder::findOrFail($id);
        $order->update(['status' => $validated['status']]);

        // If received, auto-update the stock of the medication!
        if ($validated['status'] === 'received' && $order->medication_id) {
            $medication = Medication::find($order->medication_id);
            if ($medication) {
                $medication->increment('stock_quantity', $order->quantity);
            }
        }

        return redirect()->back()->with('success', 'Purchase order status updated successfully.');
    }
}
