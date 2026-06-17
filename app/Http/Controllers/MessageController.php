<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMessageRequest;
use App\Models\Appointment;
use App\Models\Consultation;
use App\Models\LabTestRequest;
use App\Models\Medication;
use App\Models\Message;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

class MessageController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $showArchived = $request->boolean('archived');
        $search = $request->input('search', '');

        $messages = Message::with(['sender', 'receiver'])
            ->where(function ($q) use ($user) {
                $q->where('sender_id', $user->user_id)
                    ->orWhere('receiver_id', $user->user_id);
            })
            ->when($showArchived, function ($q) use ($user) {
                $q->where(function ($inner) use ($user) {
                    $inner->where(function ($q2) use ($user) {
                        $q2->where('sender_id', $user->user_id)->whereNotNull('sender_archived_at');
                    })->orWhere(function ($q2) use ($user) {
                        $q2->where('receiver_id', $user->user_id)->whereNotNull('receiver_archived_at');
                    });
                });
            }, function ($q) use ($user) {
                $q->where(function ($inner) use ($user) {
                    $inner->where(function ($q2) use ($user) {
                        $q2->where('sender_id', $user->user_id)->whereNull('sender_archived_at');
                    })->orWhere(function ($q2) use ($user) {
                        $q2->where('receiver_id', $user->user_id)->whereNull('receiver_archived_at');
                    });
                });
            })
            ->orderBy('created_at', 'desc')
            ->get();

        $peerIds = $messages->flatMap(fn ($m) => [$m->sender_id, $m->receiver_id])
            ->unique()
            ->filter(fn ($id) => $id !== $user->user_id)
            ->values();

        if ($search) {
            $usersQuery = User::where('is_active', true)
                ->where('user_id', '!=', $user->user_id)
                ->where(function ($q) use ($search) {
                    $q->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('username', 'like', "%{$search}%");
                });
        } else {
            $usersQuery = User::whereIn('user_id', $peerIds)->where('is_active', true);
        }

        $users = $usersQuery->get(['user_id', 'first_name', 'last_name', 'username'])
            ->map(function ($u) use ($user, $messages) {
                $u['unread_count'] = $messages->where('sender_id', $u->user_id)
                    ->where('receiver_id', $user->user_id)
                    ->whereNull('read_at')
                    ->count();

                $lastMessage = $messages->first(function ($m) use ($u, $user) {
                    return ($m->sender_id === $u->user_id && $m->receiver_id === $user->user_id)
                        || ($m->sender_id === $user->user_id && $m->receiver_id === $u->user_id);
                });
                $u['last_message_at'] = $lastMessage?->created_at;

                return $u;
            })
            ->sortByDesc('last_message_at')
            ->values();

        return Inertia::render('Messages/Index', [
            'messages' => $messages,
            'users' => $users,
            'filters' => [
                'search' => $search,
                'archived' => $showArchived,
            ],
        ]);
    }

    public function store(StoreMessageRequest $request)
    {
        $validated = $request->validated();
        Message::create([
            'sender_id' => Auth::id(),
            'receiver_id' => $validated['receiver_id'],
            'content' => $validated['content'],
            'metadata' => $validated['metadata'] ?? null,
        ]);

        return redirect()->back()->with('success', 'Message sent.');
    }

    public function markRead($id)
    {
        Message::where('id', $id)
            ->where('receiver_id', Auth::id())
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return redirect()->back();
    }

    public function markAllRead($userId)
    {
        Message::where('sender_id', $userId)
            ->where('receiver_id', Auth::id())
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return redirect()->back();
    }

    public function archiveConversation($userId)
    {
        $me = Auth::id();

        Message::where('sender_id', $me)->where('receiver_id', $userId)
            ->update(['sender_archived_at' => now()]);

        Message::where('sender_id', $userId)->where('receiver_id', $me)
            ->update(['receiver_archived_at' => now()]);

        return redirect()->route('messages.index')->with('success', 'Conversation archived.');
    }

    public function unarchiveConversation($userId)
    {
        $me = Auth::id();

        Message::where('sender_id', $me)->where('receiver_id', $userId)
            ->update(['sender_archived_at' => null]);

        Message::where('sender_id', $userId)->where('receiver_id', $me)
            ->update(['receiver_archived_at' => null]);

        return redirect()->route('messages.index', ['archived' => 1])->with('success', 'Conversation restored.');
    }

    public function destroy($id)
    {
        $message = Message::findOrFail($id);
        abort_unless(
            in_array(Auth::id(), [$message->sender_id, $message->receiver_id], true),
            403
        );
        $message->delete();

        return redirect()->back()->with('success', 'Message deleted.');
    }

    public function destroyConversation($userId)
    {
        $me = Auth::id();

        Message::where(function ($q) use ($me, $userId) {
            $q->where(function ($q2) use ($me, $userId) {
                $q2->where('sender_id', $me)->where('receiver_id', $userId);
            })->orWhere(function ($q2) use ($me, $userId) {
                $q2->where('sender_id', $userId)->where('receiver_id', $me);
            });
        })->delete();

        return redirect()->route('messages.index')->with('success', 'Conversation deleted.');
    }

    public function getEntities()
    {
        $user = Auth::user();
        $role = $user->role;
        $entities = [];

        if ($role !== 'patient') {
            $entities['patients'] = Patient::with('user')->limit(100)->get()->map(function ($p) {
                return [
                    'id' => $p->patient_id,
                    'label' => $p->user->first_name . ' ' . $p->user->last_name . ' (' . $p->patient_number . ')',
                    'type' => 'patient',
                ];
            });
        }

        if (in_array($role, ['admin', 'doctor', 'nurse', 'receptionist'], true)) {
            $entities['appointments'] = Appointment::with('patient.user')->latest()->limit(50)->get()->map(function ($a) {
                return [
                    'id' => $a->appointment_id,
                    'label' => 'Apt #' . $a->appointment_id . ': ' . $a->patient->user->first_name . ' (' . $a->appointment_date . ')',
                    'type' => 'appointment',
                ];
            });
        }

        if (in_array($role, ['admin', 'doctor', 'nurse'], true)) {
            $entities['consultations'] = Consultation::with('patient.user')->latest()->limit(50)->get()->map(function ($c) {
                return [
                    'id' => $c->consultation_id,
                    'label' => 'Consultation #' . $c->consultation_id . ' - ' . $c->patient->user->first_name,
                    'type' => 'consultation',
                ];
            });
        }

        if (in_array($role, ['admin', 'doctor', 'nurse', 'lab_technician'], true)) {
            $entities['lab_requests'] = LabTestRequest::with(['patient.user', 'testType'])->latest()->limit(50)->get()->map(function ($l) {
                return [
                    'id' => $l->request_id,
                    'label' => 'Lab #' . $l->request_id . ' - ' . ($l->testType->test_name ?? 'Test') . ' (' . $l->patient->user->first_name . ')',
                    'type' => 'lab_request',
                ];
            });
        }

        if (in_array($role, ['admin', 'doctor', 'nurse', 'pharmacist'], true)) {
            $entities['medications'] = Medication::limit(100)->get()->map(function ($m) {
                return [
                    'id' => $m->medication_id,
                    'label' => $m->medication_name . ' (' . $m->strength . ')',
                    'type' => 'medication',
                ];
            });
        }

        return response()->json($entities);
    }
}
