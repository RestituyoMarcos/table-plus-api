<?php

namespace App\Console\Commands;

use App\Models\Task;
use App\Notifications\TaskReminderNotification;
use Carbon\Carbon;
use Illuminate\Console\Command;

class SendTaskReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:send-task-reminders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Envia notificaciones de recordatorio para tareas pendientes que estan por vencer.';

    public function handle()
    {
        $this->info('Buscando tareas que necesitan recordatorio...');

        $tasksToNotify = Task::where('status', 'pending')
            ->whereNotNull('reminder_minutes_before')
            ->whereNull('notification_sent_at')
            ->get();

        if ($tasksToNotify->isEmpty()) {
            $this->info('Aún no hay tareas pendientes para enviar recordatorio.');
            return;
        }

        foreach ($tasksToNotify as $task) {
            $reminderTime = Carbon::parse($task->due_date)->subMinutes($task->reminder_minutes_before);

            // Comprueba si la hora del recordatorio es ahora o ya pasó.
            if ($reminderTime->isSameMinute(Carbon::now()) || $reminderTime->isPast()) {
                try {
                    $task->user->notify(new TaskReminderNotification($task));

                    $task->update(['notification_sent_at' => Carbon::now()]);

                    $this->info("Recordatorio de de tarea #{$task->id}: {$task->title}. Enviado a {$task->user->email}.");
                } catch (\Exception $e) {
                    $this->error("Error al enviar el recordatorio de la tarea #{$task->id}. Error: " . $e->getMessage());
                }
            }
        }

        $this->info('Recordatorios enviados correctamente.');
    }
}
