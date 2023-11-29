<?php

namespace App\Interfaces;

use App\Models\Project;

interface ProjectRepositoryInterface
{
    /**
     * Get one
     * @param string $identifier
     * @return Project
     */
    public function findByIdentifier(string $identifier): Project;
}
