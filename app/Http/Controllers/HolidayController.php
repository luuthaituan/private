<?php

namespace App\Http\Controllers;

use App\Interfaces\SettingRepositoryInterface;
use App\Models\Setting;
use Illuminate\Database\Eloquent\Casts\Json;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class HolidayController extends Controller
{
    /**
     * @var SettingRepositoryInterface
     */
    private SettingRepositoryInterface $settingRepository;

    /**
     * @param SettingRepositoryInterface $settingRepository
     */
    public function __construct(
        SettingRepositoryInterface $settingRepository
    ) {
        $this->settingRepository = $settingRepository;
    }

    /**
     * All holidays action
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        return response()->json(Json::decode($this->settingRepository->get(Setting::HOLIDAYS_CONFIG_PATH)));
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
        try {
            $setting = $this->settingRepository->save([
                'path' => Setting::HOLIDAYS_CONFIG_PATH,
                'value' => Json::encode($request->days)
            ]);
        } catch (\Exception) {
            return response()->json([
                'success' => false,
                'message' => 'Have an error when try to update holidays.',
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Update holidays successfully.',
            'days' => Json::decode($setting->value)
        ]);
    }
}
