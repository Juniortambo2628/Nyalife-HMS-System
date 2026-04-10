<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests\StoreContactMessageRequest;
use App\Models\ContactMessage;
use Inertia\Inertia;
use Carbon\Carbon;

class ContactMessageController extends Controller
{
    public function store(StoreContactMessageRequest $request)
    {
        $validated = $request->validated();
        ContactMessage::create($validated);

        return response()->json([
            'message' => 'Your message has been sent successfully. We will get back to you soon!',
            'status' => 'success'
        ]);
    }

    public function index()
    {
        $messages = ContactMessage::latest()->get();
        return Inertia::render('ContactMessages/Index', [
            'messages' => $messages
        ]);
    }

    public function show(ContactMessage $contactMessage)
    {
        if (!$contactMessage->read_at) {
            $contactMessage->update([
                'read_at' => now(),
                'status' => 'read'
            ]);
        }

        return Inertia::render('ContactMessages/Show', [
            'message' => $contactMessage
        ]);
    }

    public function markAsRead(ContactMessage $contactMessage)
    {
        $contactMessage->update([
            'read_at' => now(),
            'status' => 'read'
        ]);

        return back()->with('success', 'Message marked as read');
    }

    public function destroy(ContactMessage $contactMessage)
    {
        $contactMessage->delete();
        return back()->with('success', 'Message deleted successfully');
    }
}
