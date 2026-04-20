<?php

namespace App\Http\Controllers;

use App\Models\Insurance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class InsuranceController extends Controller
{
    /**
     * Display a listing of insurances for management.
     */
    public function index()
    {
        return Inertia::render('Insurances/Index', [
            'insurances' => Insurance::orderBy('sort_order')->get()
        ]);
    }

    /**
     * Show the form for creating a new insurance.
     */
    public function create()
    {
        return Inertia::render('Insurances/Create');
    }

    /**
     * API for public landing page.
     */
    public function publicList()
    {
        return response()->json(
            Insurance::where('is_active', true)
                ->orderBy('sort_order')
                ->get()
        );
    }

    /**
     * Store a new insurance.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'logo' => 'required|image|max:2048',
            'link' => 'nullable|url',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ]);

        if ($request->hasFile('logo')) {
            $path = $request->file('logo')->store('insurances', 'public');
            $validated['logo_path'] = $path;
        }

        Insurance::create($validated);
        return redirect()->route('insurances.index')->with('success', 'Insurance added successfully.');
    }

    /**
     * Show the form for editing an insurance.
     */
    public function edit($id)
    {
        $insurance = Insurance::findOrFail($id);
        return Inertia::render('Insurances/Edit', [
            'insurance' => $insurance
        ]);
    }

    /**
     * Update an insurance.
     */
    public function update(Request $request, $id)
    {
        $insurance = Insurance::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'logo' => 'nullable|image|max:2048',
            'link' => 'nullable|url',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ]);

        if ($request->hasFile('logo')) {
            // Delete old logo
            if ($insurance->logo_path) {
                Storage::disk('public')->delete($insurance->logo_path);
            }
            $path = $request->file('logo')->store('insurances', 'public');
            $validated['logo_path'] = $path;
        }

        $insurance->update($validated);
        return redirect()->route('insurances.index')->with('success', 'Insurance updated successfully.');
    }

    /**
     * Delete an insurance.
     */
    public function destroy($id)
    {
        $insurance = Insurance::findOrFail($id);
        
        if ($insurance->logo_path) {
            Storage::disk('public')->delete($insurance->logo_path);
        }
        
        $insurance->delete();

        return redirect()->back()->with('success', 'Insurance removed successfully.');
    }

    /**
     * Toggle active status.
     */
    public function toggle($id)
    {
        $insurance = Insurance::findOrFail($id);
        $insurance->update(['is_active' => !$insurance->is_active]);

        return redirect()->back()->with('success', 'Status updated.');
    }
}
