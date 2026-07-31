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
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

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

        foreach (self::entitySources() as $key => $source) {
            // 'all' = visible to every non-patient role; otherwise explicit role list.
            $allowed = $source['roles'] === 'all'
                ? $role !== 'patient'
                : in_array($role, $source['roles'], true);

            if (! $allowed) {
                continue;
            }

            $entities[$key] = $source['query']()->map(function ($model) use ($source) {
                return [
                    'id' => $source['id']($model),
                    'label' => $source['label']($model),
                    'type' => $source['type'],
                ];
            });
        }

        return response()->json($entities);
    }

    /**
     * Data-driven registry of entity sources available to the messaging
     * reference picker. Each entry exposes:
     *   - roles:   list of legacy role names allowed to see it, or 'all'
     *              (meaning every non-patient role).
     *   - query:   closure returning the Eloquent query (eager-loaded).
     *   - id, label: closures producing the payload shape used by the picker.
     *   - type:    singular lowercase identifier sent to the frontend.
     *
     * @return array<string, array{roles: array<int, string>|'all', query: \Closure, id: \Closure, label: \Closure, type: string}>
     */
    private static function entitySources(): array
    {
        return [
            'patients' => [
                'roles' => 'all',
                'type' => 'patient',
                'query' => fn () => Patient::with('user')->limit(100)->get(),
                'id' => fn ($p) => $p->patient_id,
                'label' => fn ($p) => $p->user->first_name.' '.$p->user->last_name.' ('.$p->patient_number.')',
            ],
            'appointments' => [
                'roles' => ['admin', 'doctor', 'nurse', 'receptionist'],
                'type' => 'appointment',
                'query' => fn () => Appointment::with('patient.user')->latest()->limit(50)->get(),
                'id' => fn ($a) => $a->appointment_id,
                'label' => fn ($a) => 'Apt #'.$a->appointment_id.': '.$a->patient->user->first_name.' ('.$a->appointment_date.')',
            ],
            'consultations' => [
                'roles' => ['admin', 'doctor', 'nurse'],
                'type' => 'consultation',
                'query' => fn () => Consultation::with('patient.user')->latest()->limit(50)->get(),
                'id' => fn ($c) => $c->consultation_id,
                'label' => fn ($c) => 'Consultation #'.$c->consultation_id.' - '.$c->patient->user->first_name,
            ],
            'lab_requests' => [
                'roles' => ['admin', 'doctor', 'nurse', 'lab_technician'],
                'type' => 'lab_request',
                'query' => fn () => LabTestRequest::with(['patient.user', 'testType'])->latest()->limit(50)->get(),
                'id' => fn ($l) => $l->request_id,
                'label' => fn ($l) => 'Lab #'.$l->request_id.' - '.($l->testType->test_name ?? 'Test').' ('.$l->patient->user->first_name.')',
            ],
            'medications' => [
                'roles' => ['admin', 'doctor', 'nurse', 'pharmacist'],
                'type' => 'medication',
                'query' => fn () => Medication::limit(100)->get(),
                'id' => fn ($m) => $m->medication_id,
                'label' => fn ($m) => $m->medication_name.' ('.$m->strength.')',
            ],
        ];
    }
}
