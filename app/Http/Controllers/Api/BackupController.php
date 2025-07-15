<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use SimpleXMLElement;
use Spatie\ArrayToXml\ArrayToXml;

class BackupController extends Controller
{
    public function createBackup()
    {
        $user = Auth::user();
        $tasks = $user->tasks()->get()->toArray();

        if (empty($tasks)) {
            return response()->json(['message' => 'No tiene tareas para el backup.'], 404);
        }


        $cleanedTasks = array_map(function ($task) {
            $task['title'] = htmlspecialchars($task['title'] ?? '', ENT_QUOTES, 'UTF-8');
            $task['description'] = htmlspecialchars($task['description'] ?? '', ENT_QUOTES, 'UTF-8');
            $task['status'] = htmlspecialchars($task['status'] ?? '', ENT_QUOTES, 'UTF-8');
            $task['due_date'] = htmlspecialchars($task['due_date'] ?? '', ENT_QUOTES, 'UTF-8');
            $task['notification_sent_at'] = htmlspecialchars($task['notification_sent_at'] ?? '', ENT_QUOTES, 'UTF-8');
            $task['reminder_minutes_before'] = htmlspecialchars($task['reminder_minutes_before'] ?? '', ENT_QUOTES, 'UTF-8');
            $task['deleted_at'] = htmlspecialchars($task['deleted_at'] ?? '', ENT_QUOTES, 'UTF-8');
            $task['created_at'] = htmlspecialchars($task['created_at'], ENT_QUOTES, 'UTF-8');
            $task['updated_at'] = htmlspecialchars($task['updated_at'], ENT_QUOTES, 'UTF-8');

            $nullableFields = ['notification_sent_at', 'reminder_minutes_before', 'attachment_path', 'deleted_at'];
            foreach ($nullableFields as $field) {
                if ($task[$field] === '') {
                    $task[$field] = null;
                }
            }
            return $task;
        }, $tasks);

        $xml = ArrayToXml::convert(['task' => $cleanedTasks], [
            'rootElementName' => 'tasks',
            '_attributes' => [],
        ]);

        $fileName = 'backup-tasks-' . now()->format('Y-m-d_H-i') . '.xml';

        return response($xml, 200, [
            'Content-Type' => 'application/xml',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
        ]);
    }

    public function restoreBackup(Request $request)
    {
        $request->validate([
            'backup_file' => 'required|file|mimes:xml',
        ]);

        $user = Auth::user();
        $xmlFile = $request->file('backup_file')->getContent();

        try {
            $xmlObject = new SimpleXMLElement($xmlFile);
            $restoredCount = 0;

            if (!isset($xmlObject->task)) {
                return response()->json(['message' => 'El archivo XML no contiene tareas válidas.'], 400);
            }

            foreach ($xmlObject->task as $taskNode) {
                $taskData = array_map(function ($value) {
                    return (trim($value) === '') ? null : $value;
                }, (array) $taskNode);

                Task::updateOrCreate(
                    ['id' => $taskData['id'], 'user_id' => $user->id],
                    [
                        'title' => $taskData['title'],
                        'description' => $taskData['description'],
                        'status' => $taskData['status'],
                        'due_date' => $taskData['due_date'],
                        'notification_sent_at' => $taskData['notification_sent_at'] ?? null,
                        'reminder_minutes_before' => $taskData['reminder_minutes_before'] ?? null,
                        'deleted_at' => $taskData['deleted_at'] ?? null,
                        'created_at' => $taskData['created_at'],
                        'updated_at' => $taskData['updated_at'],
                    ]
                );
                $restoredCount++;
            }

            return response()->json(['message' => "Tareas restablecidas correctamente! {$restoredCount} tareas."]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Archivo XML no esta formateado correctamente.', 'error' => $e->getMessage()], 400);
        }
    }
}
