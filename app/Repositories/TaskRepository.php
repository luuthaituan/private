<?php

namespace App\Repositories;

use App\Interfaces\TaskRepositoryInterface;
use App\Models\Task;
use App\Services\CalculateToDateService;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Facades\DB;

class TaskRepository implements TaskRepositoryInterface
{
    /**
     * @inheritDoc
     */
    public function find($id): Task
    {
        return Task::find($id);
    }

    /**
     * @inheritDoc
     */
    public function save(array $data): Task
    {
        $data = $this->prepare($data);
        $projectId = $data['project_id'];
        $task = new Task();
        $previousChildren = $task->newCollection();
        $children = [];

        try {
            DB::beginTransaction();
            if (isset($data['id']) && $data['id']) {
                $task = $this->find($data['id']);
                $previousChildren = $task->children()->get();
            }
            $task->fill([
                'title' => $data['title'],
                'jira_id' => $data['jira_id'],
                'start_date' => $data['start_date'],
                'project_id' => $projectId,
            ]);
            $task->save();

            foreach ($data['children'] as $child) {
                $model = new Task();
                if (isset($child['id']) && $child['id']) {
                    $model = $this->find($child['id']);
                }
                $model->fill([
                    'title' => $child['title'],
                    'start_date' => $child['start_date'],
                    'duration' => $child['duration'],
                    'progress' => $child['progress'],
                    'project_id' => $projectId,
                    'parent_id' => $task->id,
                ]);
                $model->save();

                $model->assignees()->detach();
                foreach ($child['assignees'] as $resource) {
                    $resourceId = is_int($resource) ? $resource : $resource['id'];
                    $model->assignees()->attach($resourceId);
                }
                $children[] = $model->with('assignees')->find($model->id);
            }

            // Delete removed records
            foreach ($previousChildren->diff($children) as $remove) {
                $remove->delete();
            }

            DB::commit();
            $task->children = $children;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }

        return $task;
    }

    /**
     * @inheritDoc
     */
    public function delete($id): int
    {
        return $this->find($id)->delete();
    }

    /**
     * Prepare data before fill to model
     *
     * @param array $data
     * @return array
     * @throws BindingResolutionException
     */
    private function prepare(array $data): array
    {
        $calculateService = app()->make(CalculateToDateService::class);

        if (isset($data['children'])) {
            usort($data['children'], function ($a, $b) {
                $aIndex = 0;
                $bIndex = 0;
                foreach (array_keys(Task::TASK_TYPES) as $index => $type) {
                    if ($a['title'] == $type) {
                        $aIndex = $index;
                    } elseif ($b['title'] == $type) {
                        $bIndex = $index;
                    }
                }

                if ($aIndex > $bIndex) {
                    return 1;
                } elseif ($aIndex < $bIndex) {
                    return -1;
                }

                return 0;
            });

            $startDate = $data['start_date'];
            foreach ($data['children'] as &$child) {
                $child['start_date'] = $startDate;
                $startDate = $calculateService->calculateToDate($startDate, (int)$child['duration'], true);
            }
        }

        return $data;
    }
}
