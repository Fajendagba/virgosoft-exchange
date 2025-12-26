<?php

namespace App\Http\Controllers\API\v1\Auth;

use App\Exceptions\UnauthorizedException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Traits\JsonResponseTrait;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    use JsonResponseTrait;

    public function register(RegisterRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();

            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => $validated['password'],
                'balance' => 10000.00,
            ]);

            $token = $user->createToken('exchange-token')->accessToken;

            $response = [
                'user' => UserResource::make($user),
                'token' => $token,
            ];

            return $this->successResponse($response, 'Registration successful', 201);
        } catch (Exception $e) {
            Log::error('Registration error: ' . $e->getMessage());
            return $this->error('Registration failed. Please try again.');
        }
    }

    public function login(LoginRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();

            $user = User::where('email', $validated['email'])->first();

            if (!$user || !Hash::check($validated['password'], $user->password)) {
                throw new UnauthorizedException('Invalid credentials');
            }

            $token = $user->createToken('exchange-token')->accessToken;

            $response = [
                'user' => UserResource::make($user),
                'token' => $token,
            ];

            return $this->successResponse($response, 'Login successful');
        } catch (UnauthorizedException $e) {
            return $this->error($e->getMessage(), 401);
        } catch (Exception $e) {
            Log::error('Login error: ' . $e->getMessage());
            return $this->error('Login failed. Please try again.');
        }
    }

    public function logout(Request $request): JsonResponse
    {
        try {
            $token = $request->user()->token();

            if ($token) {
                $token->revoke();
            }

            return $this->success('Logged out successfully');
        } catch (Exception $e) {
            Log::error('Logout error: ' . $e->getMessage());
            return $this->success('Logged out successfully');
        }
    }
}
