<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\ResourceCollection;

trait ApiResponseTrait
{
    public function successResponse($message = '', $data = [], $status = 200): JsonResponse
    {
        $isDashboard = str_starts_with(request()->route()->getName(), 'dashboard');
        if ($data instanceof ResourceCollection) {
            $response = $data->additional([
                'status' => true,
                'message' => $message,
            ])->response()->setStatusCode($status);

            $originalData = $response->getData(true);
            if (isset($originalData['links'])) {
                if (! $isDashboard) {
                    unset($originalData['links']);
                }
                $response->setData($originalData);
            }

            $originalData = $response->getData(true);
            if (isset($originalData['meta']['links'])) {
                if (! $isDashboard) {
                    unset($originalData['meta']['links']);
                }
                $response->setData($originalData);
            }

            return $response;
        }

        return response()->json([
            'status' => true,
            'message' => $message,
            'data' => $data,
        ], $status);
    }

    public function failedResponse($message = '', $errors = [], $status = 422): JsonResponse
    {
        return response()->json([
            'status' => false,
            'message' => $message,
            'errors' => $errors,
        ], $status);
    }
}
