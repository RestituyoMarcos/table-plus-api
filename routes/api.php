<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BackupController;
use App\Http\Controllers\Api\SoapController;
use App\Http\Controllers\Api\TaskController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Models\User;
use App\Models\Task;
use App\Notifications\TaskReminderNotification;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('tasks', [TaskController::class, 'tasks'])->name('tasks.getall');
 
//Auth routes
Route::post('register', [AuthController::class, 'register'])->name('auth.register');
Route::post('login', [AuthController::class, 'login'])->name('auth.login');

Route::middleware('auth:sanctum')->group(function () {
    //Authenticaction routes
    Route::post('logout', [AuthController::class, 'logout'])->name('auth.logout');
    
    Route::get('mytasks', [TaskController::class, 'index'])->name('tasks.mytasks');
    Route::post('task', [TaskController::class, 'store'])->name('tasks.store');
    Route::get('task/{task}', [TaskController::class, 'show'])->name('tasks.show');
    Route::put('task/{task}', [TaskController::class, 'update'])->name('tasks.update');
    Route::delete('task/{task}', [TaskController::class, 'destroy'])->name('tasks.destroy');

    Route::get('/test-notification/{task}', [TaskController::class, 'testNotification'])->name('tasks.testNotification');

    //Backup routes
    Route::get('/backup/create', [BackupController::class, 'createBackup']);
    Route::post('/backup/restore', [BackupController::class, 'restoreBackup']);

    //SOAP routes
    Route::post('/mock-soap-server', [SoapController::class, 'handleExternalRequest']);
    Route::post('/tasks/export-soap', [SoapController::class, 'sendTasksViaSoap']);

});
