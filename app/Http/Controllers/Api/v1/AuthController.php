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
        $success = User::where('email', $request->email)->first();

        $success['token'] = $success->createToken('hirabookapi')->accessToken;

        return $this->sendResponse($success, 'User has been logged in successfully.');
    }

    /**
     * Get authenticated user information
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function me(): JsonResponse
    {
        $user = Auth::user();

        if (!$user) {
            return $this->sendError('Unauthorized.', ['error' => 'User not authenticated'], 401);
        }

        return $this->sendResponse($user, 'Authenticated user info.');
    }

    /**
     * Refresh the access token using a refresh token
     *
     * @param RefreshTokenRequest $request
     * @return JsonResponse
     */
    public function refreshToken(RefreshTokenRequest $request): JsonResponse
    {
        try {
            $response = Http::asForm()->post(config('app.url') . '/oauth/token', [
                'grant_type' => 'refresh_token',
                'refresh_token' => $request->refresh_token,
                'client_id' => config('passport.password_client_id'),
                'client_secret' => config('passport.password_client_secret'),
                'scope' => '',
            ]);

            if ($response->failed()) {
                return $this->sendError(
                    'Token refresh failed',
                    ['error' => $response->json()['message'] ?? 'Invalid refresh token'],
                    $response->status()
                );
            }

            return $this->sendResponse($response->json(), 'Token refreshed successfully.');
        } catch (\Exception $e) {
            return $this->sendError(
                'Token refresh failed',
                ['error' => 'An unexpected error occurred'],
                500
            );
        }
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
