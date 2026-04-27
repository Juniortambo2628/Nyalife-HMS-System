<?php

namespace App\Services;

use App\Models\User;
use App\Notifications\SystemNotification;
use Spatie\Activitylog\Models\Activity;
use Illuminate\Support\Facades\Notification;

class ActivityLogger
{
    /**
     * Log an activity and notify relevant users.
     * 
     * @param string $module The module/category (appointments, consultations, lab, pharmacy, billing)
     * @param string $description Human readable description
     * @param array $properties Extra data for the log
     * @param User|null $causer The user who performed the action
     * @param mixed $subject The model being acted upon
     * @param array $notifyUserIds IDs of users to notify
     */
    public static function log($module, $description, $properties = [], $causer = null, $subject = null, $notifyUserIds = [])
    {
        // 1. Log to Activity Log
        $activity = activity()
            ->causedBy($causer ?? auth()->user());

        if ($subject instanceof \Illuminate\Database\Eloquent\Model) {
            $activity->performedOn($subject);
        }

        $activity = $activity->withProperties(array_merge($properties, ['module' => $module]))
            ->log($description);

        // 2. Send Notifications
        if (!empty($notifyUserIds)) {
            $users = User::whereIn('user_id', $notifyUserIds)->get();
            
            $notificationData = [
                'subject' => $module . ' Activity',
                'message' => $description,
                'activity_id' => $activity->id,
                'link' => self::getModuleLink($module, $subject),
            ];

            Notification::send($users, new SystemNotification($module, $notificationData));
        }
        
        return $activity;
    }

    private static function getModuleLink($module, $subject)
    {
        // Logic to return the correct URL based on module and subject
        switch ($module) {
            case 'appointments': return '/appointments';
            case 'consultations': return '/consultations';
            case 'lab': return '/lab/requests';
            case 'pharmacy': return '/pharmacy/prescriptions';
            case 'billing': return '/invoices';
            default: return '/dashboard';
        }
    }
}
