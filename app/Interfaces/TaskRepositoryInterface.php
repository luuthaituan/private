<?php

namespace App\Interfaces;

use App\Models\Task;

interface TaskRepositoryInterface
{
    /**
     * Get one
     * @param $id
     * @return Task
     */
    public function find($id): Task;

    /**
     * Create or update task
     *
     * @param array $data
     * @return Task
     * @throws \Exception
     */
    public function save(array $data): Task;

    /**
     * Delete task
     *
     * @param $id
     * @return int
     */
    public function delete($id): int;
}
