<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\EmailTemplate;
use Spatie\DatabaseMailTemplates\HasDatabaseMailTemplate;

class SystemNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $data;
    protected $module;

    /**
     * Create a new notification instance.
     */
    public function __construct($module, $data)
    {
        $this->module = $module;
        $this->data = $data;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        $channels = ['database'];
        
        // Add mail if user has email and template exists
        if ($notifiable->email) {
            $channels[] = 'mail';
        }
        
        return $channels;
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        // This will be handled by spatie/laravel-database-mail-templates
        // But for now, we'll provide a fallback or the Mailable itself.
        // Actually, the package expects a Mailable.
        // I'll create a Mailable instead.
        return (new MailMessage)
                    ->subject($this->data['subject'] ?? 'Nyalife HMS Notification')
                    ->line($this->data['message'] ?? 'You have a new activity in ' . $this->module)
                    ->action('View Dashboard', url('/dashboard'))
                    ->line('Thank you for using our application!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return array_merge($this->data, [
            'module' => $this->module,
        ]);
    }
}
