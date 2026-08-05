<?php

namespace App\Http\Middleware;

use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'auth' => [
                'user' => $request->user(),
                'permissions' => $request->user()
                    ? $request->user()->getAllPermissions()->pluck('name')->values()->all()
                    : [],
                'roles' => $request->user()
                    ? $request->user()->getRoleNames()->values()->all()
                    : [],
                'unread_notifications_count' => $request->user() ? $request->user()->unreadNotifications()->count() : 0,
                // Recent notifications for dropdown preview (both read & unread, most recent first)
                'notifications' => $request->user() ? $request->user()->notifications()->latest()->limit(5)->get()->map(function ($n) {
                    return [
                        'id' => $n->id,
                        'data' => $n->data,
                        'message' => $n->data['message'] ?? $n->data['title'] ?? 'New notification',
                        'type' => $n->data['type'] ?? $n->data['module'] ?? 'info',
                        'created_at_human' => $n->created_at->diffForHumans(),
                        'read_at' => $n->read_at,
                    ];
                }) : [],
                'module_notifications' => $request->user() ? $request->user()->unreadNotifications->groupBy(function ($n) {
                    return $n->data['module'] ?? 'general';
                })->map->count() : [],
                'unread_messages_count' => $request->user() ? Message::where('receiver_id', $request->user()->user_id)->whereNull('read_at')->count() : 0,
                // Recent messages for dropdown preview (both read & unread, most recent first)
                'recent_messages' => $request->user() ? Message::with('sender')
                    ->where('receiver_id', $request->user()->user_id)
                    ->orderBy('created_at', 'desc')
                    ->limit(5)
                    ->get()
                    ->map(function ($m) {
                        return [
                            'message_id' => $m->message_id ?? $m->id,
                            'message_text' => Str::limit($m->content, 80),
                            'sender_name' => $m->sender ? trim($m->sender->first_name.' '.$m->sender->last_name) : 'System',
                            'sender_avatar' => $m->sender?->profile_image,
                            'created_at_human' => $m->created_at->diffForHumans(),
                            'read_at' => $m->read_at,
                        ];
                    }) : [],
            ],
        ];
    }
}
