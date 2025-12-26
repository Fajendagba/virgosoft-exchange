<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

trait JsonResponseTrait
{
    public function successResponse($data, $message = null, $statusCode = Response::HTTP_OK): JsonResponse
    {
        if ($message === null) {
            $message = 'Operation successful';
        }

        $response = [
            "success" => true,
            "data" => $data,
            "message" => $message
        ];
        return response()->json($response, $statusCode);
    }

    public function success($message = null, $statusCode = Response::HTTP_OK): JsonResponse
    {
        if ($message === null) {
            $message = 'Operation successful';
        }

        $response = [
            "success" => true,
            "message" => $message
        ];
        return response()->json($response, $statusCode);
    }

    public function errorResponse($data = null, $message = null, $statusCode = Response::HTTP_BAD_REQUEST): JsonResponse
    {
        if ($message === null) {
            $message = 'Operation failed';
        }

        return response()->json([
            "success" => false,
            "message" => $message,
            "data" => $data
        ], $statusCode);
    }

    public function error($message = null, $statusCode = Response::HTTP_BAD_REQUEST): JsonResponse
    {
        if ($message === null) {
            $message = 'Operation failed';
        }

        return response()->json([
            "success" => false,
            "message" => $message
        ], $statusCode);
    }
}
