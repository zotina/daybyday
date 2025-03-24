<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Carbon\Carbon;

class ProjectRest extends Controller
{
   
    public function index(): JsonResponse
    {
        try {
            $projects = Project::with(['assignee', 'status', 'client'])
                ->select([
                    'external_id',
                    'title',
                    'created_at',
                    'deadline',
                    'user_assigned_id',
                    'status_id',
                    'client_id'
                ])
                ->get()
                ->map(function ($project) {
                    return [
                        'external_id' => $project->external_id,
                        'title' => $project->title,
                        'created_at' => $project->created_at ? Carbon::parse($project->created_at)->format('Y-m-d H:i:s') : null,
                        'deadline' => $project->deadline ? Carbon::parse($project->deadline)->format('Y-m-d H:i:s') : null,
                        'assigned_user' => $project->assignee ? $project->assignee->name : null,
                        'status' => $project->status ? [
                            'title' => $project->status->title,
                            'color' => $project->status->color
                        ] : null,
                        'client' => $project->client ? $project->client->company_name : null
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $projects,
                'message' => 'Projects retrieved successfully'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving projects: ' . $e->getMessage()
            ], 500);
        }
    }
}