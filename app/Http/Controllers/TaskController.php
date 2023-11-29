<?php

namespace App\Http\Controllers;

use App\Interfaces\TaskRepositoryInterface;
use App\Models\Task;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class TaskController extends Controller
{
    /**
     * @var TaskRepositoryInterface
     */
    private TaskRepositoryInterface $taskRepository;

    /**
     * TaskController constructor.
     *
     * @param TaskRepositoryInterface $taskRepository
     */
    public function __construct(
        TaskRepositoryInterface $taskRepository
    ) {
        $this->taskRepository = $taskRepository;
    }

    /**
     * Save task action
     *
     * @param Request $request
     * @return JsonResponse
     * @throws \Exception
     */
    public function store(Request $request): JsonResponse
    {
        return response()->json($this->taskRepository->save($request->toArray()));
    }

    /**
     * Update task action
     *
     * @param $id
     * @param Request $request
     * @return JsonResponse
     * @throws \Exception
     */
    public function update($id, Request $request): JsonResponse
    {
        $data = $request->toArray();
        $data['id'] = $id;

        return response()->json($this->taskRepository->save($data));
    }

    /**
     * Delete task action
     *
     * @param $id
     * @return JsonResponse
     */
    public function delete($id): JsonResponse
    {
        $this->taskRepository->delete($id);

        return response()->json([
            "success" => true,
            "message" => "Delete task successfully"
        ]);
    }

    /**
     * Sync from jira action
     *
     * @throws GuzzleException
     */
    public function syncFromJira($projectId): JsonResponse
    {
        $data = [];
        $client = new Client();
        $tasks = Task::where('project_id', $projectId)
            ->whereNotNull('jira_id')
            ->where(function($query) {
                $query->whereNull('status')
                    ->orWhereNotIn('status', Task::DONE_STATUSES);
            })
            ->get()
        ;

        foreach ($tasks as &$task) {
            try {
                $request = $client->request(
                    'GET',
                    $this->getSyncUrl($task->jira_id),
                    [
                        'headers' => [
                            'Authorization' => "Bearer " . env('JIRA_TOKEN')
                        ]
                    ]
                );

                $response = $request->getBody()->getContents();
                $status = Arr::get(json_decode($response, true), 'fields.status.name');
                $task->status = $status;
                $task->save();
                $data[$task->id] = $task->with(['children.assignees'])->find($task->id);
            } catch (\Exception) {
                continue;
            }
        }

        return response()->json($data);
    }

    /**
     * Get sync api url by jira_id
     *
     * @param $jiraId
     * @return string
     */
    private function getSyncUrl($jiraId): string
    {
        return env('JIRA_URL') . '/rest/api/latest/issue/' . $jiraId;
    }
}
