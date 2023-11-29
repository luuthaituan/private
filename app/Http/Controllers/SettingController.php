<?php

namespace App\Http\Controllers;

use App\Interfaces\SettingRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SettingController extends Controller
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
     * Save task action
     *
     * @param Request $request
     * @return JsonResponse
     * @throws \Exception
     */
    public function store(Request $request): JsonResponse
    {
        if ($this->settingRepository->save($request->toArray())) {
            return response()->json([
                'success' => true,
                'message' => 'Update settings successfully.',
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Have an error when try to update settings.',
        ]);
    }
}
