<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\FollowUpResource;
use App\Models\FollowUp;
use Illuminate\Http\Request;

class FollowUpController extends Controller
{
    public function index(Request $request)
    {
        $followUps = FollowUp::with(['patient.user', 'consultation.doctor.user', 'createdBy'])
            ->filteredQuery($request)
            ->latest('follow_up_date')
            ->paginate($request->integer('per_page', 15));

        return FollowUpResource::collection($followUps);
    }

    public function upcoming(Request $request)
    {
        $followUps = FollowUp::with(['patient.user', 'consultation.doctor.user'])
            ->upcoming()
            ->when($request->patient_id, fn ($q) => $q->where('patient_id', $request->patient_id))
            ->orderBy('follow_up_date')
            ->limit($request->integer('limit', 20))
            ->get();

        return FollowUpResource::collection($followUps);
    }

    public function show($id)
    {
        $followUp = FollowUp::with(['patient.user', 'consultation.doctor.user', 'createdBy'])
            ->findOrFail($id);

        return FollowUpResource::make($followUp);
    }
}
