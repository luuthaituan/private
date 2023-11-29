<?php

namespace App\Http\Controllers;

use App\Models\Resource;
use GuzzleHttp\Client;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class ResourceController extends Controller
{
    /**
     * All projects action
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        return response()->json(Resource::all());
    }

    /**
     * Save project action
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $avatar = '';
        $client = new Client();
        try {
            $response = $client->request(
                'GET',
                $this->getApiUrl($request->account),
                [
                    'headers' => [
                        'Authorization' => "Bearer " . env('JIRA_TOKEN')
                    ]
                ]
            )->getBody()->getContents();
            $avatar = Arr::get(json_decode($response, true), 'avatarUrls.32x32');
        } catch (\Exception) {}

        $resource = new Resource();
        $resource->account = $request->account;
        $resource->name = $request->name;
        $resource->email = $request->email;
        $resource->avatar = $avatar;
        $resource->save();

        return response()->json($resource);
    }

    /**
     * Get sync api url by jira_id
     *
     * @param $jiraId
     * @return string
     */
    private function getApiUrl($account): string
    {
        return env('JIRA_URL') . '/rest/api/latest/user/?username=' . $account;
    }

    /**
     * Update project action
     *
     * @param $id
     * @param Request $request
     * @return JsonResponse
     */
    public function update($id, Request $request): JsonResponse
    {
        $resource = Resource::find($id);
        $resource->account = $request->account;
        $resource->name = $request->name;
        $resource->email = $request->email;
        $resource->save();

        return response()->json($resource);
    }

    /**
     * Delete project action
     *
     * @param $id
     * @return JsonResponse
     */
    public function delete($id): JsonResponse
    {
        $resource = Resource::find($id);
        $resource->delete();

        return response()->json([
            "success" => true,
            "message" => "Delete resource successfully"
        ]);
    }
}
