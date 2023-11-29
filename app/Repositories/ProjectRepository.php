<?php

namespace App\Repositories;

use App\Interfaces\ProjectRepositoryInterface;
use App\Models\Project;

class ProjectRepository implements ProjectRepositoryInterface
{
    /**
     * @inheritDoc
     */
    public function findByIdentifier(string $identifier): Project
    {
        return Project::findByIdentifier($identifier);
    }
}
