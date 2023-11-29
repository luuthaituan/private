<?php

namespace App\Repositories;

use App\Interfaces\SettingRepositoryInterface;
use App\Models\Setting;
use Illuminate\Database\RecordsNotFoundException;

class SettingRepository implements SettingRepositoryInterface
{
    /**
     * @inheritDoc
     */
    public function find(string $path, string $scope = Setting::SCOPE_DEFAULT, int $scope_id = 0): ?Setting
    {
        return Setting::find($path, $scope, $scope_id);
    }

    /**
     * @inheritDoc
     */
    public function get(string $path, string $scope = Setting::SCOPE_DEFAULT, int $scope_id = 0): string
    {
        return (string)$this->find($path, $scope, $scope_id)?->value;
    }

    /**
     * @inheritDoc
     */
    public function save(array $data): Setting
    {
        $scope = $data['scope'] ?? Setting::SCOPE_DEFAULT;
        $scopeId = isset($data['scope_id']) ? (int)$data['scope_id'] : 0;
        $path = $data['path'] ?? '';

        if (!$path) {
            throw new RecordsNotFoundException("Settings path are required.");
        }

        // Check record exists
        $setting = $this->find($path, $scope, $scopeId);
        if (!$setting) {
            $setting = new Setting();
        }

        $setting->fill($data)->save();

        return $setting;
    }
}
