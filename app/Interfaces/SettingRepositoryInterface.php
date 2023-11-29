<?php

namespace App\Interfaces;

use App\Models\Setting;

interface SettingRepositoryInterface
{
    /**
     * Get one
     * @param string $path
     * @param string $scope
     * @param int $scope_id
     * @return Setting
     */
    public function find(string $path, string $scope = Setting::SCOPE_DEFAULT, int $scope_id = 0): ?Setting;

    /**
     * Get value
     * @param string $path
     * @param string $scope
     * @param int $scope_id
     * @return string
     */
    public function get(string $path, string $scope = Setting::SCOPE_DEFAULT, int $scope_id = 0): string;

    /**
     * Save setting
     *
     * @param array $data
     * @return Setting
     */
    public function save(array $data): Setting;
}
