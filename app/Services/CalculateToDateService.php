<?php

namespace App\Services;

use App\Interfaces\SettingRepositoryInterface;
use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Json;

class CalculateToDateService
{
    /**
     * Calculate to date service
     *
     * @param SettingRepositoryInterface $settingRepository
     */
    public function __construct(
        private SettingRepositoryInterface $settingRepository
    ) {}

    /**
     * Calculate end date by start date and duration
     *
     * @param string $startDate
     * @param int $duration
     * @param bool $next
     * @return string
     */
    public function calculateToDate(string $startDate, int $duration, bool $next = false): string
    {
        $date = new Carbon($startDate);
        $holidays = (array)Json::decode(
            $this->settingRepository->get(Setting::HOLIDAYS_CONFIG_PATH)
        );
        $count = 0;
        $duration = $next ? $duration : $duration - 1;

        while ($count < $duration) {
            $date = $date->addDay();
            if ($date->dayOfWeek !== 0 && $date->dayOfWeek !== 6 && !in_array($date->format('Y-m-d'), $holidays)) {
                $count++;
            }
        }

        return $date->format('Y-m-d');
    }
}
