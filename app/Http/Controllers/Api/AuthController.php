<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
     use ApiResponseTrait;


    // Controller methods will go here
    public function register(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
        ]);


        if ($validator->fails()) {
            return $this->errorResponse(
                'Validation Error',
                422,
                $validator->errors()
            );
        }


        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
        ]);


        $token = $user->createToken('auth_token')->plainTextToken;


        return $this->successResponse([
            'user' => $user,
            'token' => $token,
        ], 'User registered successfully', 201);
    }


     public function login(Request $request): JsonResponse
    {
        //  Validation
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string',
        ]);


        if ($validator->fails()) {
            return $this->errorResponse(
                'Validation Error',
                422,
                $validator->errors()
            );
        }


        //  Attempt login
        if (! Auth::attempt($request->only('email', 'password'))) {
            return $this->errorResponse('Unauthorized', 401);
        }


        //  Success
        $user = Auth::user();
        $token = $user->createToken('auth_token')->plainTextToken;


        return $this->successResponse([
            'user' => $user,
            'token' => $token,
        ], 'User logged in successfully');
    }


    public function logout(Request $request): JsonResponse
    {
        //  authenticated user
        $user = $request->user();


        if (! $user || ! $user->currentAccessToken()) {
            return $this->errorResponse('Unauthorized', 401);
        }


        //  current token revoke
        $user->currentAccessToken()->delete();


        return $this->successResponse(
            null,
            'User logged out successfully'
        );
    }

}
