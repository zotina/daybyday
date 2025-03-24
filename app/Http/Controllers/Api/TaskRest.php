<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Carbon\Carbon;

class TaskRest extends Controller
{
    public function __construct()
    {
        $this->middleware('task.update.status', ['only' => ['update']]);
        $this->middleware('task.assigned', ['only' => ['update']]);
    }

    public function index(Request $request): JsonResponse
    {
        try {
            $perPage = $request->query('per_page', 10); // Nombre d'éléments par page, par défaut 10
            $tasks = Task::with(['user', 'status', 'client'])
                ->select([
                    'external_id',
                    'title',
                    'created_at',
                    'deadline',
                    'user_assigned_id',
                    'status_id',
                    'client_id'
                ])
                ->paginate($perPage);

            $formattedTasks = $tasks->map(function ($task) {
                return [
                    'external_id' => $task->external_id,
                    'title' => $task->title,
                    'created_at' => $task->created_at ? Carbon::parse($task->created_at)->format('Y-m-d H:i:s') : null,
                    'deadline' => $task->deadline ? Carbon::parse($task->deadline)->format('Y-m-d H:i:s') : null,
                    'assigned_user' => $task->user ? $task->user->name : null,
                    'status' => $task->status ? [
                        'title' => $task->status->title,
                        'color' => $task->status->color
                    ] : null,
                    'client' => $task->client ? $task->client->company_name : null
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $formattedTasks,
                'pagination' => [
                    'current_page' => $tasks->currentPage(),
                    'per_page' => $tasks->perPage(),
                    'total' => $tasks->total(),
                    'last_page' => $tasks->lastPage(),
                ],
                'message' => 'Tasks retrieved successfully'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving tasks: ' . $e->getMessage()
            ], 500);
        }
    }
}
