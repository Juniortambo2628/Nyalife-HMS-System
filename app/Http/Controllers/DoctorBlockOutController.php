<?php

namespace App\Http\Controllers;

use App\Models\DoctorBlockOut;
use App\Models\Staff;
use App\Traits\HasBulkActions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class DoctorBlockOutController extends Controller
{
    use HasBulkActions;

    public function index(Request $request)
    {
        $user = Auth::user();
        $query = DoctorBlockOut::with('doctor.user');

        if ($user->role === 'doctor') {
            $staff = Staff::where('user_id', $user->user_id)->first();
            if ($staff) {
                $query->where('doctor_id', $staff->staff_id);
            }
        }

        if ($request->filled('doctor_id')) {
            $query->where('doctor_id', $request->doctor_id);
        }

        if ($request->filled('date')) {
            $query->where('block_date', $request->date);
        }

        $blockOuts = $query->orderBy('block_date', 'desc')->paginate(15)->withQueryString();

        return Inertia::render('DoctorBlockOuts/Index', [
            'blockOuts' => $blockOuts,
            'filters' => $request->only(['doctor_id', 'date']),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'doctor_id' => 'required|integer|exists:staff,staff_id',
            'block_date' => 'required|date|after_or_equal:today',
            'start_time' => 'nullable|date_format:H:i',
            'end_time' => 'nullable|date_format:H:i|after_or_equal:start_time',
            'reason' => 'nullable|string|max:255',
        ]);

        DoctorBlockOut::create($validated);

        return redirect()->back()->with('success', 'Block-out date added successfully.');
    }

    public function destroy($id)
    {
        $blockOut = DoctorBlockOut::findOrFail($id);
        $blockOut->delete();

        return redirect()->back()->with('success', 'Block-out date removed successfully.');
    }

    public function bulkDelete(Request $request)
    {
        $validated = $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'integer',
        ]);

        $deleted = $this->bulkDelete(DoctorBlockOut::class, 'id', $validated['ids']);

        return redirect()->back()->with('success', "{$deleted} block-out date(s) removed.");
    }
}
