<?php

namespace App\Notifications;

use Filament\Notifications\Actions\Action;
use Filament\Notifications\Notification as FilamentNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

abstract class BaseNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        protected string $title,
        protected string $message,
        protected string $status = 'info',
        protected ?string $actionUrl = null,
        protected ?string $actionLabel = null,
        protected ?string $icon = null
    ) {}

    public function via($notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $mail = (new MailMessage)
            ->subject($this->title)
            ->line($this->message);

        if ($this->actionUrl && $this->actionLabel) {
            $mail->action($this->actionLabel, $this->actionUrl);
        }

        return $mail;
    }

    public function toDatabase($notifiable): array
    {
        return [
            'title' => $this->title,
            'message' => $this->message,
            'status' => $this->status,
            'action_url' => $this->actionUrl,
            'action_label' => $this->actionLabel,
            'icon' => $this->icon,
        ];
    }

    public function toFilament($notifiable): ?FilamentNotification
    {
        $notification = FilamentNotification::make()
            ->title($this->title)
            ->body($this->message)
            ->status($this->status)
            ->duration(10000);

        if ($this->icon) {
            $notification->icon($this->icon);
        }

        if ($this->actionUrl && $this->actionLabel) {
            $notification->actions([
                Action::make('view')
                    ->button()
                    ->label($this->actionLabel)
                    ->url($this->actionUrl),
            ]);
        }

        return $notification;
    }
}
