<?php

namespace App\Http\Controllers;

use App\Models\Medication;
use App\Models\MedicationCategory;
use Illuminate\Http\Request;
use Inertia\Inertia;

class MedicationController extends Controller
{
    public function index(Request $request)
    {
        $query = Medication::query();

        if ($request->search) {
            $query->where('medication_name', 'like', "%{$request->search}%")
                  ->orWhere('generic_name', 'like', "%{$request->search}%");
        }

        $medications = $query->paginate(15);

        return Inertia::render('Inventory/Index', [
            'medications' => $medications,
            'filters' => $request->only(['search'])
        ]);
    }

    public function show($id)
    {
        $medication = Medication::findOrFail($id);
        return Inertia::render('Inventory/Show', [
            'medication' => $medication
        ]);
    }
}
