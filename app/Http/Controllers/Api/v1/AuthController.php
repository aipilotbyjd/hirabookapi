<?php

namespace App\Http\Controllers\Api\v1;

use Illuminate\Http\Request;
use App\Http\Controllers\BaseController;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class AuthController extends BaseController
{
    public function login(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'identifier' => 'required',
                'password' => 'required',
            ]);

            if ($validator->fails()) {
                return $this->sendError('Validation Error.', $validator->errors());
            }

            $identifier = $request->identifier;
            $loginField = filter_var($identifier, FILTER_VALIDATE_EMAIL) ? 'email' : 'phone';

            if (Auth::attempt([$loginField => $identifier, 'password' => $request->password])) {
                $user = User::select('id', 'first_name', 'last_name', 'email', 'phone', 'profile_image')->where('id', Auth::id())->first();
                $token = $user->createToken($user->email ?? 'hirabook')->accessToken;
                $response = [
                    'user' => $user,
                    'token' => $token
                ];
                return $this->sendResponse($response, 'User has been logged in successfully.');
            } else {
                return $this->sendError('Unauthorized.', [], 401);
            }
        } catch (\Exception $e) {
            logError('AuthController', 'login', $e->getMessage());
            return $this->sendError('Something went wrong', [], 500);
        }
    }

    public function register(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'first_name' => 'required|string|max:255',
                'last_name' => 'required|string|max:255',
                'email' => 'nullable|email|unique:users,email|required_without:phone',
                'phone' => 'nullable|regex:/^([0-9\s\-\+\(\)]*)$/|min:10|unique:users,phone|required_without:email',
                'password' => 'required',
            ]);

            if ($validator->fails()) {
                return $this->sendError('Validation Error.', $validator->errors());
            }

            $input = $request->all();

            if (empty($input['email']) && !empty($input['phone'])) {
                $input['email'] = $input['phone'] . '@hirabook.com';
            }

            $input['first_name'] = $request->first_name ?? fake()->firstName();
            $input['last_name'] = $request->last_name ?? fake()->lastName();

            $input['password'] = Hash::make($input['password']);
            $user = User::create($input);

            $token = $user->createToken($user->email ?? 'hirabook')->accessToken;

            $user = [
                'id' => $user->id,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'email' => $user->email,
                'phone' => $user->phone,
                'profile_image' => $user->profile_image,
            ];

            $success = [
                'user' => $user,
                'token' => $token
            ];

            return $this->sendResponse($success, 'User registered successfully.');
        } catch (\Exception $e) {
            logError('AuthController', 'register', $e->getMessage());
            return $this->sendError('Something went wrong', [], 500);
        }
    }

    public function logout(Request $request)
    {
        try {
            $user = Auth::user();
            if (!$user) {
                return $this->sendError('User not authenticated', [], 401);
            }

            $user->token()->revoke();

            return $this->sendResponse([], 'User logged out successfully');
        } catch (\Exception $e) {
            logError('AuthController', 'logout', $e->getMessage());
            return $this->sendError('Something went wrong', [], 500);
        }
    }

    public function user(Request $request)
    {
        try {
            $user = Auth::user();

            $output = [
                'id' => $user->id,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'email' => $user->email,
                'phone' => $user->phone,
                'profile_image' => $user->profile_image,
            ];

            return $this->sendResponse($output, 'User fetched successfully');
        } catch (\Exception $e) {
            logError('AuthController', 'user', $e->getMessage());
            return $this->sendError('Something went wrong', [], 500);
        }
    }

    public function phoneLogin(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'phone' => 'required|regex:/^([0-9\s\-\+\(\)]*)$/|min:10'
            ]);

            if ($validator->fails()) {
                return $this->sendError('Validation Error.', $validator->errors());
            }

            // Check if user exists
            $user = User::where('phone', $request->phone)->first();
            if (!$user) {
                //register user
                $user = User::create([
                    'email' => $request->phone . '@hirabook.com',
                    'phone' => $request->phone,
                    'otp' => rand(1000, 9999),
                    'otp_expiry' => now()->addMinutes(10)
                ]);
            }

            // Generate and store OTP
            $otp = rand(1000, 9999);
            $user->otp = $otp;
            $user->otp_expiry = now()->addMinutes(10); // OTP valid for 10 minutes
            $user->save();

            $apiKey = env('INTERAKT_API_KEY');

            if (!$apiKey) {
                throw new \Exception('Interakt API key not configured');
            }

            $response = Http::withHeaders([
                'Authorization' => 'Basic ' . $apiKey,
                'Content-Type' => 'application/json'
            ])->post('https://api.interakt.ai/v1/public/message/', [
                        'countryCode' => '+91',
                        'phoneNumber' => $request->phone,
                        // 'fullPhoneNumber' => '+91' . $request->phone,
                        'callbackData' => 'hiraapi',
                        'type' => 'Template',
                        'template' => [
                            'name' => 'hiraapi',
                            'languageCode' => 'en_US',
                            'bodyValues' => [
                                $otp
                            ],
                            'buttonValues' => [
                                '1' => [
                                    'Copy code'
                                ]
                            ]
                        ]
                    ]);

            Log::info($response->json());

            $result = $response->json()['result'];

            Log::info($result);

            if ($result) {
                return $this->sendResponse([
                    'message' => 'OTP sent successfully',
                    'expires_in' => 10 // minutes
                ], 'OTP sent successfully');
            } else {
                return $this->sendError('OTP not sent', [], 400);
            }

            // return $this->sendResponse([
            //     'message' => 'OTP sent successfully',
            //     'expires_in' => 10 // minutes
            // ], 'OTP sent successfully');
        } catch (\Exception $e) {
            logError('AuthController', 'phoneLogin', $e->getMessage());
            return $this->sendError('Something went wrong', [], 500);
        }
    }

    public function verifyOtp(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'phone' => 'required|regex:/^([0-9\s\-\+\(\)]*)$/|min:10',
                'otp' => 'required|numeric|digits:4'
            ]);

            if ($validator->fails()) {
                return $this->sendError('Validation Error.', $validator->errors());
            }

            $user = User::where('phone', $request->phone)->first();
            if (!$user) {
                return $this->sendError('User not found', [], 404);
            }

            if ($user->otp != $request->otp) {
                return $this->sendError('Invalid OTP', [], 400);
            }

            if ($user->otp_expiry < now()) {
                return $this->sendError('OTP has expired', [], 400);
            }

            // Clear OTP after successful verification
            $user->otp = null;
            $user->otp_expiry = null;
            $user->save();

            $user->token = $user->createToken($user->email ?? 'hirabook')->accessToken;

            $userData = [
                'id' => $user->id,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'email' => $user->email,
                'phone' => $user->phone,
                'profile_image' => $user->profile_image,
            ];

            $output = [
                'user' => $userData,
                'token' => $user->token
            ];

            return $this->sendResponse($output, 'OTP verified successfully');
        } catch (\Exception $e) {
            logError('AuthController', 'verifyOtp', $e->getMessage());
            return $this->sendError('Something went wrong', [], 500);
        }
    }

    public function forgotPassword(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'email' => 'required|email|exists:users,email',
            ]);

            if ($validator->fails()) {
                return $this->sendError('Validation Error.', $validator->errors());
            }

            $user = User::where('email', $request->email)->first();

            // Generate reset token
            $resetToken = Str::random(60);
            $user->password_reset_token = $resetToken;
            $user->password_reset_expires_at = now()->addHours(24); // Token valid for 24 hours
            $user->save();

            // Send reset password email
            Mail::send('emails.forgot-password', [
                'user' => $user,
                'resetToken' => $resetToken,
                'resetUrl' => env('FRONTEND_URL') . '/reset-password?token=' . $resetToken
            ], function ($message) use ($user) {
                $message->to($user->email)
                    ->subject('Reset Your Password');
            });

            return $this->sendResponse([
                'message' => 'Password reset link has been sent to your email'
            ], 'Reset email sent successfully');

        } catch (\Exception $e) {
            logError('AuthController', 'forgotPassword', $e->getMessage());
            return $this->sendError('Something went wrong', [], 500);
        }
    }

    public function resetPassword(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'token' => 'required|string',
                'password' => 'required|min:6',
                'password_confirmation' => 'required|same:password'
            ]);

            if ($validator->fails()) {
                return $this->sendError('Validation Error.', $validator->errors());
            }

            $user = User::where('password_reset_token', $request->token)
                ->where('password_reset_expires_at', '>', now())
                ->first();

            if (!$user) {
                return $this->sendError('Invalid or expired reset token', [], 400);
            }

            // Update password
            $user->password = Hash::make($request->password);
            $user->password_reset_token = null;
            $user->password_reset_expires_at = null;
            $user->save();

            return $this->sendResponse([
                'message' => 'Password has been reset successfully'
            ], 'Password reset successful');

        } catch (\Exception $e) {
            logError('AuthController', 'resetPassword', $e->getMessage());
            return $this->sendError('Something went wrong', [], 500);
        }
    }

    public function updateProfile(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'first_name' => $request->has('first_name') ? 'nullable|string|max:255|regex:/^[a-zA-Z\s]+$/' : '',
                'last_name' => $request->has('last_name') ? 'nullable|string|max:255|regex:/^[a-zA-Z\s]+$/' : '',
                'email' => $request->has('email') ? 'nullable|email|max:255|unique:users,email,' . Auth::id() : '',
                'phone' => $request->has('phone') ? 'nullable|regex:/^([0-9\s\-\+\(\)]*)$/|min:10|max:15|unique:users,phone,' . Auth::id() : '',
                'address' => $request->has('address') ? 'nullable|string|min:5|max:500' : '',
                'profile_image' => $request->hasFile('profile_image') ? 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048|dimensions:min_width=100,min_height=100' : '',
            ]);

            if ($validator->fails()) {
                return $this->sendError('Validation Error.', $validator->errors());
            }

            $user = User::findOrFail(Auth::id());

            // Update only provided fields
            if ($request->has('first_name'))
                $user->first_name = $request->first_name;
            if ($request->has('last_name'))
                $user->last_name = $request->last_name;
            if ($request->has('email'))
                $user->email = $request->email;
            if ($request->has('phone'))
                $user->phone = $request->phone;
            if ($request->has('address'))
                $user->address = $request->address;

            //log image
            Log::info($request->profile_image . $request->hasFile('profile_image'));

            if ($request->hasFile('profile_image')) {
                // Delete old image if exists
                if ($user->profile_image) {
                    Storage::disk('public')->delete('profile_images/' . $user->profile_image);
                }

                // Store new image
                $imageName = time() . '_' . Str::random(10) . '.' . $request->profile_image->extension();
                $request->profile_image->storeAs('profile_images', $imageName, 'public');
                $user->profile_image = $imageName;
            }

            $user->save();

            $userData = [
                'id' => $user->id,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'email' => $user->email,
                'phone' => $user->phone,
                'profile_image' => $user->profile_image,
                'address' => $user->address,
            ];

            return $this->sendResponse($userData, 'Profile updated successfully');
        } catch (\Exception $e) {
            logError('AuthController', 'updateProfile', $e->getMessage());
            return $this->sendError('Something went wrong', [], 500);
        }
    }

    public function profile(Request $request)
    {
        try {
            $user = Auth::user();

            $output = [
                'id' => $user->id,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'email' => $user->email,
                'phone' => $user->phone,
                'profile_image' => $user->profile_image,
                'address' => $user->address,
            ];

            return $this->sendResponse($output, 'Profile fetched successfully');
        } catch (\Exception $e) {
            logError('AuthController', 'profile', $e->getMessage());
            return $this->sendError('Something went wrong', [], 500);
        }
    }

    public function googleLogin(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'id_token' => 'required|string'
            ]);

            if ($validator->fails()) {
                return $this->sendError('Validation Error.', $validator->errors(), 422);
            }

            $idToken = $request->id_token;

            // Step 1: Verify the token with Google's tokeninfo endpoint
            $verifyResponse = Http::get('https://oauth2.googleapis.com/tokeninfo', [
                'id_token' => $idToken
            ]);

            if (!$verifyResponse->successful()) {
                return $this->sendError('Invalid Google token', [], 401);
            }

            $tokenInfo = $verifyResponse->json();

            // Step 2: Validate token claims
            // Check if token is expired
            if (isset($tokenInfo['exp']) && $tokenInfo['exp'] < time()) {
                return $this->sendError('Token has expired', [], 401);
            }

            // Optional: Verify your app's client ID (if you want to ensure the token was issued for your application)
            // if ($tokenInfo['aud'] !== env('GOOGLE_CLIENT_ID')) {
            //    return $this->sendError('Invalid audience for token', [], 401);
            // }

            // Step 3: Use the verified user information
            if (!isset($tokenInfo['email'])) {
                return $this->sendError('Email not available in Google user data', [], 400);
            }

            // Find or create user based on email
            $user = User::where('email', $tokenInfo['email'])->first();

            if (!$user) {
                // Create new user
                $user = User::create([
                    'email' => $tokenInfo['email'],
                    'first_name' => $tokenInfo['given_name'] ?? '',
                    'last_name' => $tokenInfo['family_name'] ?? '',
                    'profile_image' => $tokenInfo['picture'] ?? null,
                    'google_id' => $tokenInfo['sub'] ?? null,
                    'password' => Hash::make(Str::random(16))
                ]);
            } else {
                // Update existing user
                $user->google_id = $tokenInfo['sub'];
                if (empty($user->profile_image) && isset($tokenInfo['picture'])) {
                    $user->profile_image = $tokenInfo['picture'];
                }
                $user->save();
            }

            $token = $user->createToken($user->email ?? 'hirabook')->accessToken;

            // Format user data consistent with other auth methods
            $userData = [
                'id' => $user->id,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'email' => $user->email,
                'phone' => $user->phone,
                'profile_image' => $user->profile_image,
            ];

            $success = [
                'user' => $userData,
                'token' => $token
            ];

            return $this->sendResponse($success, 'Google login successful');
        } catch (\Exception $e) {
            logError('AuthController', 'googleLogin', $e->getMessage());
            return $this->sendError('Something went wrong', [], 500);
        }
    }

    public function verifyToken(Request $request)
    {
        try {
            $user = Auth::user();
            $userData = [
                'id' => $user->id,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'email' => $user->email,
                'phone' => $user->phone,
                'profile_image' => $user->profile_image,
            ];

            return $this->sendResponse($userData, 'Token verified successfully');
        } catch (\Exception $e) {
            logError('AuthController', 'verifyToken', $e->getMessage());
            return $this->sendError('Something went wrong', [], 500);
        }
    }
}
