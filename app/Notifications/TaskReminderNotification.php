<?php

namespace App\Notifications;

use App\Models\Task;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TaskReminderNotification extends Notification
{
    use Queueable;

    protected $task;

    public function __construct(Task $task)
    {
        $this->task = $task;
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $dueDate = $this->task->due_date->format('d/m/Y \a \l\a\s H:i');

        return (new MailMessage)
                    ->subject('Recordatorio de Tarea: ' . $this->task->title)
                    ->greeting('¡Hola, ' . $notifiable->name . '!')
                    ->line('Este es un recordatorio para tu tarea: "' . $this->task->title . '".')
                    ->line('Fecha de vencimiento: ' . $dueDate)
                    ->action('Ver Tarea', url('/task/' . $this->task->id))
                    ->line('¡No lo olvides!');
    }

    public function toArray(object $notifiable): array
    {
        return ['mail'];
    }
}
