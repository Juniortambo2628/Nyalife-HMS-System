<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PaymentResource;
use App\Models\Payment;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function index(Request $request)
    {
        $payments = Payment::with(['invoice.patient.user', 'receivedBy'])
            ->filteredQuery($request)
            ->latest('payment_date')
            ->paginate($request->integer('per_page', 15));

        return PaymentResource::collection($payments);
    }

    public function show($id)
    {
        $payment = Payment::with(['invoice.patient.user', 'invoice.items', 'receivedBy'])
            ->findOrFail($id);

        return PaymentResource::make($payment);
    }
}
