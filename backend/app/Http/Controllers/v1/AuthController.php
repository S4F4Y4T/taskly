<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\Auth\LoginRequest;
use App\Http\Requests\V1\Auth\RegistrationRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function me(): UserResource
    {
        return UserResource::make(auth()->user());
    }
    public function register(RegistrationRequest $request): JsonResponse
    {
        $user = User::query()->create($request->validated());
        return self::success('User created successfully', 201, data:UserResource::make($user));
    }

    public function login(LoginRequest $request): JsonResponse
    {
        if (!auth()->attempt($request->validated())) {
            return self::error('Invalid credentials', 401);
        }

        $token = auth()->user()->createToken('api-token')->plainTextToken;

        return self::success('Logged in successfully', 200, data:['token' => $token]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return self::success('Logged out successfully');
    }
}
