<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    /**
     * All projects action
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        return response()->json(Project::all());
    }

    /**
     * Save project action
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $project = new Project();
        $project->name = $request->name;
        $project->save();

        return response()->json($project);
    }

    /**
     * Update project action
     *
     * @param $id
     * @param Request $request
     * @return JsonResponse
     */
    public function update($id, Request $request): JsonResponse
    {
        $project = Project::find($id);
        $project->name = $request->name;
        $project->save();

        return response()->json($project);
    }

    /**
     * Delete project action
     *
     * @param $id
     * @return JsonResponse
     */
    public function delete($id): JsonResponse
    {
        $project = Project::find($id);
        $project->delete();

        return response()->json([
            "success" => true,
            "message" => "Delete project successfully"
        ]);
    }
}
