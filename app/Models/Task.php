<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @OA\Schema(
 * schema="Task",
 * title="Task",
 * description="Task model",
 * @OA\Property(property="id", type="integer", readOnly="true", example="1"),
 * @OA\Property(property="user_id", type="integer", example="1"),
 * @OA\Property(property="title", type="string", example="My First Task"),
 * @OA\Property(property="description", type="string", example="A description for the task."),
 * @OA\Property(property="status", type="string", example="pending"),
 * @OA\Property(property="due_date", type="string", format="date-time", example="2025-12-31 23:59:59"),
 * @OA\Property(property="created_at", type="string", format="date-time", readOnly="true"),
 * @OA\Property(property="updated_at", type="string", format="date-time", readOnly="true")
 * )
 */
class Task extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'title',
        'description',
        'status',
        'due_date',
        'notification_sent_at',
        'reminder_minutes_before',
        'attachment_path',
    ];

    protected $casts = [
        'due_date' => 'datetime',
        'notification_sent_at' => 'datetime',
    ];

    /**
     * Obteniendo el usuario para cada Tarea.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
