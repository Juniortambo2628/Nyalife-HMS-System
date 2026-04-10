<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreLabTestTypeRequest;
use App\Http\Requests\UpdateLabTestTypeRequest;
use App\Models\LabTestType;
use Illuminate\Http\Request;
use Inertia\Inertia;

class LabTestTypeController extends Controller
{
    public function index()
    {
        return Inertia::render('Lab/Tests/Index', [
            'tests' => LabTestType::latest()->get()
        ]);
    }

    public function create()
    {
        return Inertia::render('Lab/Tests/Form');
    }

    public function store(StoreLabTestTypeRequest $request)
    {
        $validated = $request->validated();
        LabTestType::create($validated);

        return redirect()->route('lab-tests.index')->with('success', 'Lab test type created successfully.');
    }

    public function edit($id)
    {
        $test = LabTestType::findOrFail($id);
        return Inertia::render('Lab/Tests/Form', [
            'test' => $test
        ]);
    }

    public function update(UpdateLabTestTypeRequest $request, $id)
    {
        $test = LabTestType::findOrFail($id);
        $validated = $request->validated();
        $test->update($validated);

        return redirect()->route('lab-tests.index')->with('success', 'Lab test type updated successfully.');
    }

    public function destroy($id)
    {
        $test = LabTestType::findOrFail($id);
        
        // Soft delete/deactivate if there are requests? 
        // For now, let's just allow deletion if no requests exist, 
        // or just toggle is_active if it's easier.
        // Let's just toggle is_active for safety.
        $test->update(['is_active' => !$test->is_active]);

        return redirect()->route('lab-tests.index')->with('success', 'Lab test status toggled.');
    }
}
