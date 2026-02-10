<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Requests\UserRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(UserRequest $request)
    {
        Log::info('User registration attempt', [
            'email' => $request->input('email'),
            'ip' => $request->ip(),
        ]);

        try {
            $validated = $request->validated();

            Log::debug('User registration validated', [
                'email' => $validated['email'],
                'name' => $validated['name'],
            ]);

            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'role' => 'user',
                'dni' => $validated['dni'], 
                'password' => Hash::make($validated['password']), 
                'address' => $validated['address'],
                'phone' => $validated['phone'],
                'city' => $validated['city'],
            ]);

            Log::info('User registered successfully', [
                'user_id' => $user->id,
                'email' => $user->email,
            ]);

            return response()->json([
                'user' => $user,
            ], 201);
        } catch (\Exception $e) {
            Log::error('User registration failed', [
                'email' => $request->input('email'),
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    public function login(Request $request)
    {
        Log::info('User login attempt', [
            'email' => $request->input('email'),
            'ip' => $request->ip(),
        ]);

        try {
            $credentials = $request->validate([
                'email' => ['required', 'email'],
                'password' => ['required', 'string'],
            ]);

            if (!Auth::attempt($credentials, $request->boolean('remember'))) {
                Log::warning('User login failed - invalid credentials', [
                    'email' => $request->input('email'),
                    'ip' => $request->ip(),
                ]);

                throw ValidationException::withMessages([
                    'email' => ['The provided credentials are incorrect.'],
                ]);
            }

            $request->session()->regenerate();

            Log::info('User logged in successfully', [
                'user_id' => $request->user()->id,
                'email' => $request->user()->email,
            ]);

            return response()->json([
                'ok' => true,
                'user' => $request->user(),
            ]);
        } catch (ValidationException $e) {
            Log::warning('User login validation error', [
                'email' => $request->input('email'),
                'errors' => $e->errors(),
            ]);
            throw $e;
        } catch (\Exception $e) {
            Log::error('User login error', [
                'email' => $request->input('email'),
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    public function logout(Request $request)
    {
        Auth::guard('web')->logout();   // force session guard

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json(['ok' => true]);
    }

    public function user(Request $request)
    {
        return response()->json([
            'user' => $request->user(),
        ]);
    }

    public function createAdmin(UserRequest $request)
    {
        Log::info('Admin creation attempt', [
            'requester_id' => $request->user()?->id,
            'ip' => $request->ip(),
        ]);

        try {
            $adminExists = User::where('role', 'admin')->exists();

            if ($adminExists) {
                $user = $request->user();

                if (!$user) {
                    Log::warning('Admin creation failed - unauthenticated', ['ip' => $request->ip()]);
                    return response()->json(['message' => 'Unauthenticated.'], 401);
                }

                if ($user->role !== 'admin') {
                    Log::warning('Admin creation forbidden - insufficient permissions', [
                        'requester_id' => $user->id,
                        'requester_role' => $user->role,
                        'ip' => $request->ip(),
                    ]);
                    return response()->json(['message' => 'Forbidden.'], 403);
                }
            }

            $validated = $request->validated();

            Log::debug('Admin creation validated', [
                'email' => $validated['email'],
                'name' => $validated['name'],
            ]);

            $admin = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'role' => 'admin',
                'password' => Hash::make($validated['password']),
                'address' => $validated['address'],
                'phone' => $validated['phone'],
                'city' => $validated['city'],
            ]);

            Log::info('Admin created successfully', [
                'admin_id' => $admin->id,
                'admin_email' => $admin->email,
                'created_by' => $request->user()?->id,
            ]);

            return response()->json([
                'user' => $admin,
            ], 201);
        } catch (\Exception $e) {
            Log::error('Admin creation failed', [
                'requester_id' => $request->user()?->id,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }
}
