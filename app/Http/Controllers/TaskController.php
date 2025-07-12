<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;

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
        $page = $request->get('page', 1); // Obtener la página actual para la clave

        // Crear una clave única para la caché que incluya al usuario y la página
        $cacheKey = "tasks.user.{$user->id}.page.{$page}";
    
        $tasks = Cache::remember($cacheKey, now()->addMinutes(10), function () use ($request, $user) {
            // Esta función solo se ejecuta si la caché está vacía
    
            $query = $user->tasks()->getQuery();
    
            // Aplicar filtros
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
        ]);

        $taskData = $validated;
        $taskData['user_id'] = Auth::id();

        // Manejo del archivo adjunto
        if ($request->hasFile('attachment')) {
            $path = $request->file('attachment')->store('attachments', 'public');
            $taskData['attachment_path'] = $path;
        }

        $task = Task::create($taskData);

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
        // Asegurarse de que el usuario es propietario de la tarea
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
        ]);

        // Manejo del archivo adjunto
        if ($request->hasFile('attachment')) {
            // Eliminar el archivo antiguo si existe
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
}
