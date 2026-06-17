<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Staff;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AppointmentSlotController extends Controller
{
    public function index(Request $request)
    {
        $validated = $request->validate([
            'doctor_id' => 'nullable|integer|exists:staff,staff_id',
            'date' => 'required|date|after_or_equal:today',
        ]);

        $date = $validated['date'];
        $doctorId = $validated['doctor_id'] ?? Staff::whereHas('user.roleRelation', function ($q) {
            $q->where('role_name', 'doctor');
        })->value('staff_id');

        if (! $doctorId) {
            return response()->json(['data' => [], 'message' => 'No doctor available.']);
        }

        $booked = Appointment::where('doctor_id', $doctorId)
            ->whereDate('appointment_date', $date)
            ->whereNotIn('status', ['cancelled', 'no_show'])
            ->pluck('appointment_time')
            ->map(fn ($time) => Carbon::parse($time)->format('H:i'))
            ->all();

        $slots = [];
        $cursor = Carbon::parse($date . ' 08:00');
        $end = Carbon::parse($date . ' 17:00');

        while ($cursor <= $end) {
            $label = $cursor->format('H:i');
            if (! in_array($label, $booked, true)) {
                $slots[] = [
                    'time' => $label,
                    'label' => $cursor->format('g:i A'),
                    'available' => true,
                ];
            }
            $cursor->addMinutes(30);
        }

        return response()->json([
            'doctor_id' => (int) $doctorId,
            'date' => $date,
            'data' => $slots,
        ]);
    }
}
