<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\AppointmentResource;
use App\Services\AppointmentQueryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AppointmentController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $query = AppointmentQueryService::scopedFor($user);
        AppointmentQueryService::applyFilters($query, $request);

        $appointments = $query->latest('appointment_date')
            ->paginate($request->integer('per_page', 15));

        return AppointmentResource::collection($appointments);
    }
}
