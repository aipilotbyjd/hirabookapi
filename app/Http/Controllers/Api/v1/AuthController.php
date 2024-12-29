<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\LoginRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\RefreshTokenRequest;
use App\Http\Controllers\Api\V1\BaseController as BaseController;

class AuthController extends BaseController
{
    /**
     * User registration
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        $userData = $request->validated();

        $userData['email_verified_at'] = now();
        $user = User::create($userData);

        $success['token'] = $user->createToken('hirabookapi')->accessToken;

        return $this->sendResponse($success, 'User has been registered successfully.');
    }

    /**
     * Login user
     */
    public function login(LoginRequest $request): JsonResponse
    {
        if (!Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
            return $this->sendError('Unauthorized.', ['error' => 'Invalid credentials'], 401);
        }

        //get user by email
        $success['user'] = User::where('email', $request->email)->first();

        $success['token'] = $success['user']->createToken('hirabookapi')->accessToken;

        return $this->sendResponse($success, 'User has been logged in successfully.');
    }

    /**
     * Login user
     *
     * @param  LoginRequest  $request
     */
    public function me(): JsonResponse
    {
        $user = auth()->user();
        return $this->sendResponse($user, 'Authenticated user info.');
    }

    /**
     * refresh token
     *
     * @return void
     */
    public function refreshToken(RefreshTokenRequest $request): JsonResponse
    {
        $response = Http::asForm()->post(env('APP_URL') . '/oauth/token', [
            'grant_type' => 'refresh_token',
            'refresh_token' => $request->refresh_token,
            'client_id' => env('PASSPORT_PASSWORD_CLIENT_ID'),
            'client_secret' => env('PASSPORT_PASSWORD_SECRET'),
            'scope' => '',
        ]);

        return $this->sendResponse($response->json(), 'Token refreshed successfully.');
    }

    /**
     * Logout
     */
    public function logout(): JsonResponse
    {
        Auth::user()->tokens()->delete();
        return $this->sendResponse([], 'Logged out successfully.');
    }
}
