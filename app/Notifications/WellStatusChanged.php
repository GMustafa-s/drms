<?php

namespace App\Notifications;

use App\Models\Well;
use Filament\Notifications\Actions\Action;
use Filament\Notifications\Notification as FilamentNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WellStatusChanged extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        protected Well $well,
        protected string $action,
        protected ?string $message = null
    ) {}

    public function via($notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Well Status Update - {$this->well->lease}")
            ->line("Well {$this->well->lease} has been {$this->action}.")
            ->line($this->message ?? '')
            ->action('View Well', url("/wells/{$this->well->id}"));
    }

    public function toDatabase($notifiable): array
    {
        return [
            'well_id' => $this->well->id,
            'action' => $this->action,
            'message' => $this->message,
        ];
    }

    public function toFilament($notifiable): ?FilamentNotification
    {
        return FilamentNotification::make()
            ->title("Well Status Update - {$this->well->lease}")
            ->icon('heroicon-o-beaker')
            ->body("Well {$this->well->lease} has been {$this->action}.")
            ->actions([
                Action::make('view')
                    ->button()
                    ->label('View Well')
                    ->url(route('filament.admin.resources.wells.edit', ['record' => $this->well])),
            ])
            ->status($this->getNotificationStatus())
            ->duration(10000);
    }

    protected function getNotificationStatus(): string
    {
        return match ($this->action) {
            'created' => 'success',
            'deleted' => 'danger',
            'published' => 'success',
            'unpublished' => 'warning',
            default => 'info',
        };
    }
}
