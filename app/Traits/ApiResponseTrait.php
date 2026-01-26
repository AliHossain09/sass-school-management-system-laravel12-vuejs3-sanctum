<?php


namespace App\Traits;


trait ApiResponseTrait
{
    protected function successResponse($data = null, string $message = 'Request successful', int $code = 200)
    {
        return response()->json([
            'status' => 'success',
            'message' => $message,
            'data' => $data,
        ], $code);
    }


    protected function errorResponse(string $message = 'Something went wrong', int $code = 400, $errors = null)
    {
        $response = [
            'status' => 'error',
            'message' => $message,
        ];


        if (! is_null($errors)) {
            $response['errors'] = $errors;
        }


        return response()->json($response, $code);
    }
}
