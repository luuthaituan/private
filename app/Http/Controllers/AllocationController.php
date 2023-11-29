<?php

namespace App\Http\Controllers;

use App\Models\AllocateHistory;
use App\Models\Allocation;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class AllocationController extends Controller
{
    /**
     * Allocate resources to a project action
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $project = $request->project;
        $resources = [];
        $histories = [];
        $today = date('Y-m-d');
        foreach ($request->resources as $resource) {
            $resources[] = [
                'project_id' => $project['id'],
                'resource_id' => $resource,
            ];
            $histories[] = [
                'project_id' => $project['id'],
                'resource_id' => $resource,
                'from' => $today,
            ];
        }
        Allocation::insert($resources);
        AllocateHistory::whereNull('to')->update(['to' => $today]);
        AllocateHistory::insert($histories);

        return response()->json([
            "success" => true,
            "message" => sprintf('Allocated %d resource(s) to "%s" project successfully', count($resources), $project['name'])
        ]);
    }
}
