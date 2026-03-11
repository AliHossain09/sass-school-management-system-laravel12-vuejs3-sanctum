<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Services\AuthService;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
     use ApiResponseTrait;

    public function __construct(protected AuthService $authService)
    {
    }

    // Controller methods will go here
    public function register(RegisterRequest $request): JsonResponse
    {
        $result = $this->authService->register($request->validated());

        return $this->successResponse(
            $result['data'],
            $result['message'],
            201
        );
    }


     public function login(LoginRequest $request): JsonResponse
    {
        $result = $this->authService->login($request->validated());

        if (! $result) {
            return $this->errorResponse('Unauthorized', 401);
        }

        return $this->successResponse(
            $result['data'],
            $result['message']
        );
    }


    public function logout(Request $request): JsonResponse
    {
        $ok = $this->authService->logout($request->user());

        if (! $ok) {
            return $this->errorResponse('Unauthorized', 401);
        }

        return $this->successResponse(
            null,
            'User logged out successfully'
        );
    }

}
