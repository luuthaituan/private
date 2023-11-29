<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\JsonResponse;

class DashboardController extends Controller
{
    public function lowestWorkload(): JsonResponse
    {
        return response()->json([]);
    }

    public function mostRequests(): JsonResponse
    {
        return response()->json([]);
    }

    public function mostPendingTasks(): JsonResponse
    {
        $tasks = Task::whereNull('start_date')
            ->with('project')
            ->orderBy('created_at', 'ASC')
            ->limit(5)
            ->get()
        ;

        return response()->json($tasks);
    }
}
