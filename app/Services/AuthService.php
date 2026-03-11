<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthService
{
    public function register(array $data): array
    {
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        return [
            'data' => [
                'user' => $user,
                'token' => $user->createToken('auth_token')->plainTextToken,
            ],
            'message' => 'User registered successfully',
        ];
    }

    public function login(array $credentials): ?array
    {
        if (! Auth::attempt($credentials)) {
            return null;
        }

        /** @var \App\Models\User $user */
        $user = Auth::user();

        return [
            'data' => [
                'user' => $user,
                'token' => $user->createToken('auth_token')->plainTextToken,
            ],
            'message' => 'User logged in successfully',
        ];
    }

    public function logout(?User $user): bool
    {
        if (! $user || ! $user->currentAccessToken()) {
            return false;
        }

        $user->currentAccessToken()->delete();

        return true;
    }
}

