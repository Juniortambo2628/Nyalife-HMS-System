<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreContactMessageRequest;
use App\Models\ContactMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;

class ContactMessageController extends Controller
{
    public function store(StoreContactMessageRequest $request)
    {
        $validated = $request->validated();
        ContactMessage::create($validated);

        return response()->json([
            'message' => 'Your message has been sent successfully. We will get back to you soon!',
            'status' => 'success',
        ]);
    }

    public function index()
    {
        $messages = ContactMessage::latest()->get();

        return Inertia::render('ContactMessages/Index', [
            'messages' => $messages,
        ]);
    }

    public function show(ContactMessage $contactMessage)
    {
        if (! $contactMessage->read_at) {
            $contactMessage->update([
                'read_at' => now(),
                'status' => 'read',
            ]);
        }

        $contactMessage->load('replier');

        return Inertia::render('ContactMessages/Show', [
            'message' => $contactMessage,
        ]);
    }

    public function markAsRead(ContactMessage $contactMessage)
    {
        $contactMessage->update([
            'read_at' => now(),
            'status' => 'read',
        ]);

        return back()->with('success', 'Message marked as read');
    }

    public function destroy(ContactMessage $contactMessage)
    {
        $contactMessage->delete();

        return back()->with('success', 'Message deleted successfully');
    }

    public function reply(Request $request, ContactMessage $contactMessage)
    {
        $request->validate([
            'reply_message' => 'required|string|min:5',
        ]);

        $contactMessage->update([
            'reply' => $request->reply_message,
            'replied_at' => now(),
            'replied_by' => auth()->id(),
            'status' => 'replied',
        ]);

        // Send email if configured
        try {
            Mail::raw($request->reply_message, function ($mail) use ($contactMessage) {
                $mail->to($contactMessage->email)
                    ->subject('Re: Your Inquiry — Nyalife Hospital');
            });
        } catch (\Exception $e) {
            // Log but don't fail — email delivery is best-effort
            \Log::warning('Contact reply email failed: '.$e->getMessage());
        }

        return back()->with('success', 'Reply sent successfully to '.$contactMessage->email);
    }
}
