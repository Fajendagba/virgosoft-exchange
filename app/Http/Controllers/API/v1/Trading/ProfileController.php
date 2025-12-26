<?php

namespace App\Http\Controllers\API\v1\Trading;

use App\Http\Controllers\Controller;
use App\Http\Resources\AssetResource;
use App\Http\Resources\UserResource;
use App\Traits\JsonResponseTrait;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ProfileController extends Controller
{
    use JsonResponseTrait;

    public function __invoke(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            $response = [
                'user' => UserResource::make($user),
                'assets' => AssetResource::collection($user->assets),
            ];

            return $this->successResponse($response, 'Profile retrieved successfully');
        } catch (Exception $e) {
            Log::error('Profile retrieval error: ' . $e->getMessage());
            return $this->error('Failed to retrieve profile');
        }
    }
}
