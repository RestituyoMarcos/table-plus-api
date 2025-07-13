<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Notifications\TaskReminderNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

/**
 * @OA\Info(
 * version="1.0.0",
 * title="Table Plus API",
 * description="Documentación de API para el proyecto Table Plus."
 * )
 *
 * @OA\SecurityScheme(
 * securityScheme="bearerAuth",
 * type="http",
 * scheme="bearer"
 * )
 */
class TaskController extends Controller
{

    /**
     * @OA\Get(
     * path="/api/tasks",
     * summary="Lista todas las tareas",
     * description="Obtiene una lista de todas las tareas.",
     * operationId="getTasks",
     * tags={"Tasks"},
     * security={{"bearerAuth":{}}},
     * @OA\Response(
     * response=200,
     * description="Successful operation",
     * @OA\JsonContent(
     * type="array",
     * @OA\Items(ref="#/components/schemas/Task")
     * )
     * ),
     * @OA\Response(
     * response=401,
     * description="Unauthenticated"
     * )
     * )
     */
    public function tasks()
    {
        return Task::all();
    }

    /**
     * @OA\Get(
     * path="/api/mytasks",
     * summary="Lista todas las tareas del usuario logueado con paginación y filtros",
     * description="Obtiene una lista de tareas del usuario logueado, con soporte para paginación y filtros por título, estado y fecha de vencimiento.",
     * tags={"Tasks"},
     * security={{"bearerAuth":{}}},
     * @OA\Response(
     * response=200,
     * description="Successful operation",
     * @OA\JsonContent(
     * type="object",
     * @OA\Property(
     * property="data",
     * type="array",
     * @OA\Items(ref="#/components/schemas/Task")
     * ),
     * @OA\Property(property="links", type="object"),
     * @OA\Property(property="meta", type="object")
     * )
     * ),
     * @OA\Response(
     * response=401,
     * description="Unauthenticated"
     * )
     * )
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $page = $request->get('page', 1);

        $cacheKey = "tasks.user.{$user->id}.page.{$page}";
    
        $tasks = Cache::remember($cacheKey, now()->addMinutes(10), function () use ($request, $user) {
            
            $query = $user->tasks()->getQuery();
    
            $query->when($request->title, fn($q, $title) => $q->where('title', 'like', "%{$title}%"));
            $query->when($request->status, fn($q, $status) => $q->where('status', $status));
            $query->when($request->date, fn($q, $date) => $q->whereDate('due_date', $date));
    
            return $query->orderBy('due_date', 'desc')->paginate(15);
        });
    
        return response()->json($tasks);
    }

    /**
     * @OA\Post(
     * path="/api/task",
     * summary="Crea una nueva tarea",
     * description="Crea una nueva tarea asociada al usuario logueado. Requiere autenticación.",
     * tags={"Tasks"},
     * security={{"bearerAuth":{}}},
     * @OA\RequestBody(
     * required=true,
     * @OA\JsonContent(ref="#/components/schemas/Task")
     * ),
     * @OA\Response(response=201, description="Task created successfully", @OA\JsonContent(ref="#/components/schemas/Task")),
     * @OA\Response(response=422, description="Validation error")
     * )
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'due_date' => 'required|date',
            'status' => 'nullable|string|in:pending,completed',
            'attachment' => 'nullable|file|mimes:pdf,jpg,png|max:5120', // Max 5MB
            'reminder_minutes_before' => [
                'nullable',
                'integer',
                Rule::in([5, 10, 15, 20, 30])
            ], 
        ]);

        $taskData = $validated;
        $user = Auth::user();
        $taskData['user_id'] = $user->id;

        if ($request->hasFile('attachment')) {
            $path = $request->file('attachment')->store('attachments', 'public');
            $taskData['attachment_path'] = $path;
        }

        $task = Task::create($taskData);
        // $user->notify(new TaskReminderNotification($task));

        return response()->json($task, 201);
    }

    /**
     * @OA\Get(
     * path="/api/task/{id}",
     * summary="Obtiene los detalles de una tarea específica",
     * description="Obtiene los detalles de una tarea específica. Requiere autenticación y autorización.",
     * tags={"Tasks"},
     * security={{"bearerAuth":{}}},
     * @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     * @OA\Response(response=200, description="Task details", @OA\JsonContent(ref="#/components/schemas/Task")),
     * @OA\Response(response=404, description="Task not found")
     * )
     */
    public function show(Task $task)
    {
        if (Gate::denies('view', $task)) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        return response()->json($task);
    }

    /**
     * @OA\Put(
     * path="/api/task/{id}",
     * summary="Actualiza una tarea existente",
     * description="Actualiza una tarea existente. Requiere autenticación y autorización.",
     * tags={"Tasks"},
     * security={{"bearerAuth":{}}},
     * @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     * @OA\RequestBody(
     * required=true,
     * @OA\JsonContent(ref="#/components/schemas/Task")
     * ),
     * @OA\Response(response=200, description="Task updated successfully", @OA\JsonContent(ref="#/components/schemas/Task")),
     * @OA\Response(response=404, description="Task not found")
     * )
     */
    public function update(Request $request, Task $task)
    {
        if (Gate::denies('update', $task)) {
            return response()->json(['message' => 'Forbidden'], 403);
        }
        
        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|required|string',
            'due_date' => 'sometimes|required|date',
            'status' => 'sometimes|required|string|in:pending,completed',
            'attachment' => 'nullable|file|mimes:pdf,jpg,png|max:5120',
            'reminder_minutes_before' => [
                'nullable', 'integer', Rule::in([5, 10, 15, 20, 30])
            ],
        ]);

        if ($request->has('due_date')) {
            $validated['notification_sent_at'] = null;
        }

        if ($request->hasFile('attachment')) {
            if ($task->attachment_path) {
                Storage::disk('public')->delete($task->attachment_path);
            }
            $path = $request->file('attachment')->store('attachments', 'public');
            $validated['attachment_path'] = $path;
        }
        
        $task->update($validated);

        return response()->json($task);
    }

    /**
     * @OA\Delete(
     * path="/api/tasks/{id}",
     * summary="Elimina una tarea existente",
     * description="Elimina una tarea existente. Requiere autenticación y autorización.",
     * tags={"Tasks"},
     * security={{"bearerAuth":{}}},
     * @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     * @OA\Response(response=204, description="Task deleted successfully"),
     * @OA\Response(response=404, description="Task not found")
     * )
     */
    public function destroy(Task $task)
    {
        if (Gate::denies('delete', $task)) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $task->delete();

        return response()->json(null, 204);
    }

    /**
     * @OA\Get(
     * path="/api/test-notification",
     * summary="Prueba de notificación",
     * description="Envía una notificación de prueba al primer usuario y su primera tarea.",
     * tags={"Notifications"},
     * security={{"bearerAuth":{}}},
     * @OA\Response(response=200, description="Notification sent successfully"),
     * @OA\Response(response=404, description="User or task not found")
     * )
     */
    public function testNotification(Task $task){

       $user = Auth::user();

        if ($user && $task) {
            $user->notify(new TaskReminderNotification($task));
            return 'Notificación de prueba enviada.';
        }

        return 'No se encontraron usuarios o tareas para enviar la notificación.';
    }
}
