<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\Patient;
use App\Models\Staff;
use App\Models\User;
use App\Support\Permissions;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class AppointmentQueryService
{
    /**
     * Base appointment query scoped to the authenticated user's access level.
     *
     * @param  list<string>  $with
     */
    public static function scopedFor(User $user, array $with = ['patient.user', 'doctor.user']): Builder
    {
        $query = Appointment::with($with);

        if ($user->can(Permissions::VIEW_OWN_RECORDS) && ! $user->can(Permissions::MANAGE_APPOINTMENTS)) {
            $patientId = Patient::where('user_id', $user->user_id)->value('patient_id');
            $query->forPatient($patientId);
        } elseif ($user->hasRole('doctor')) {
            $staffId = Staff::where('user_id', $user->user_id)->value('staff_id');
            $query->forDoctor($staffId);
        }

        return $query;
    }

    public static function applyFilters(Builder $query, Request $request): Builder
    {
        if ($request->filled('status')) {
            $query->status($request->status);
        }

        if ($request->filled('date')) {
            $query->whereDate('appointment_date', $request->date);
        }

        if ($request->filled('quick_filter')) {
            switch ($request->quick_filter) {
                case 'today':
                    $query->whereDate('appointment_date', today());
                    break;
                case 'upcoming':
                    $query->whereDate('appointment_date', '>', today());
                    break;
                case 'overdue':
                    $query->where('status', 'scheduled')
                        ->where(function ($q) {
                            $q->whereDate('appointment_date', '<', today())
                                ->orWhere(function ($sq) {
                                    $sq->whereDate('appointment_date', today())
                                        ->whereTime('appointment_time', '<', now());
                                });
                        });
                    break;
            }
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereHas('patient.user', function ($pq) use ($search) {
                    $pq->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%");
                })->orWhereHas('doctor.user', function ($dq) use ($search) {
                    $dq->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%");
                });
            });
        }

        return $query;
    }
}
