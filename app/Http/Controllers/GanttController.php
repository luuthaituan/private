<?php

namespace App\Http\Controllers;

use App\Interfaces\ProjectRepositoryInterface;
use App\Interfaces\TaskRepositoryInterface;
use App\Models\Resource;
use App\Models\Task;
use Illuminate\Http\JsonResponse;

class GanttController extends Controller
{
    /**
     * @var ProjectRepositoryInterface
     */
    private ProjectRepositoryInterface $projectRepository;

    /**
     * @var TaskRepositoryInterface
     */
    private $taskRepository;

    /**
     * GanttController constructor
     *
     * @param ProjectRepositoryInterface $projectRepository
     * @param TaskRepositoryInterface $taskRepository
     */
    public function __construct(
        ProjectRepositoryInterface $projectRepository,
        TaskRepositoryInterface $taskRepository
    ) {
        $this->projectRepository = $projectRepository;
        $this->taskRepository = $taskRepository;
    }

    /**
     * View project action
     *
     * @param $project
     * @return JsonResponse
     */
    public function execute($project): JsonResponse
    {
        $project = $this->projectRepository->findByIdentifier($project);

        $tasks = Task::addProjectFilter($project)
            ->whereNull('parent_id')
            ->with(['children.assignees'])
            ->get()
            ->keyBy('id')
        ;

        $allocatedResources = Resource::leftJoin('allocations', 'resources.id', '=', 'allocations.resource_id')
            ->where('allocations.project_id', $project->id)
            ->get(['resources.*'])
            ->keyBy('id')
        ;

        $allocatedResourceIds = [];
        foreach ($allocatedResources as $resource) {
            $allocatedResourceIds[] = $resource->id;
        }

        $availableResources = Resource::whereNotIn('id', $allocatedResourceIds)
            ->get()
            ->keyBy('id')
        ;

        return response()->json([
            'project' => $project,
            'tasks' => $tasks,
            'allocated_resources' => $allocatedResources,
            'available_resources' => $availableResources,
        ]);
    }
}
