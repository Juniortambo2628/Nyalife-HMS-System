<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\MedicalProcedure;

class MedicalProcedureController extends Controller
{
    public function index()
    {
        $procedures = MedicalProcedure::orderBy('name')->get();
        return Inertia::render('Admin/MedicalProcedures/Index', [
            'procedures' => $procedures
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:medical_procedures,name',
            'description' => 'nullable|string',
            'category' => 'required|string',
            'standard_fee' => 'required|numeric|min:0',
        ]);

        MedicalProcedure::create($validated);
        return back()->with('success', 'Medical procedure added successfully.');
    }

    public function update(Request $request, $id)
    {
        $procedure = MedicalProcedure::findOrFail($id);
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:medical_procedures,name,' . $id . ',procedure_id',
            'description' => 'nullable|string',
            'category' => 'required|string',
            'standard_fee' => 'required|numeric|min:0',
        ]);

        $procedure->update($validated);
        return back()->with('success', 'Medical procedure updated successfully.');
    }

    public function toggle($id)
    {
        $procedure = MedicalProcedure::findOrFail($id);
        $procedure->update(['is_active' => !$procedure->is_active]);
        return back()->with('success', 'Procedure status updated.');
    }

    public function destroy($id)
    {
        $procedure = MedicalProcedure::findOrFail($id);
        $procedure->delete();
        return back()->with('success', 'Medical procedure deleted successfully.');
    }
}
