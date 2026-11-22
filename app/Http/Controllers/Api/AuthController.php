<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Throwable;

class AuthController extends Controller
{
    // Register
    public function register(RegisterRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();

            $studentRole = Role::where('name', 'student')->first();

            if (! $studentRole) {
                return response()->json([
                    'message' => 'Student role is not configured.',
                ], 500);
            }

            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'role_id' => $studentRole->id,
            ]);

            Auth::login($user);

            if ($request->hasSession()) {
                $request->session()->regenerate();
            }

            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'message' => 'Registration successful.',
                'user' => $user->load('role'),
                'token' => $token,
            ], 201);
        } catch (Throwable $e) {
            return response()->json([
                'message' => 'Registration failed. Please try again.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // Login
    public function login(LoginRequest $request): JsonResponse
    {
        try {
            $credentials = $request->validated();

            if (! Auth::attempt($credentials)) {
                return response()->json([
                    'message' => 'Invalid email or password.',
                ], 401);
            }

            if ($request->hasSession()) {
                $request->session()->regenerate();
            }

            $user = $request->user()->load('role');
            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'message' => 'Login successful.',
                'user' => $user,
                'token' => $token,
            ]);
        } catch (Throwable $e) {
            return response()->json([
                'message' => 'Login failed. Please try again.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // Get authenticated user
    public function user(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            if (! $user) {
                return response()->json([
                    'message' => 'Unauthenticated.',
                ], 401);
            }

            return response()->json([
                'message' => 'User retrieved successfully.',
                'user' => $user->load('role'),
            ]);
        } catch (Throwable $e) {
            return response()->json([
                'message' => 'Failed to retrieve user.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // Logout
    public function logout(Request $request): JsonResponse
    {
        try {
            $token = $request->user()?->currentAccessToken();
            if ($token && method_exists($token, 'delete')) {
                $token->delete();
            }

            Auth::guard('web')->logout();

            if ($request->hasSession()) {
                $request->session()->invalidate();
                $request->session()->regenerateToken();
            }

            return response()->json([
                'message' => 'Logout successful.',
            ]);
        } catch (Throwable $e) {
            return response()->json([
                'message' => 'Logout failed. Please try again.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
