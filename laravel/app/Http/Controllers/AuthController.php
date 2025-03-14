<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /**
     * Регистрация нового пользователя.
     */
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        // Создаём пользователя
        $user = User::create([
            'name' => $request->input('name'),
            'email' => $request->input('email'),
            'password' => Hash::make($request->input('password')),
        ]);

        // Генерируем Sanctum-токен
        $tokenResult = $user->createToken('authToken');
        $plainTextToken = $tokenResult->plainTextToken;

        // Устанавливаем срок действия токена (например, 30 минут)
        $lastToken = $user->tokens()->latest('id')->first();
        $lastToken->expires_at = now()->addMinutes(30);
        $lastToken->save();

        // НЕ возвращаем expires_at, чтобы фронтенд о нём не знал
        return response()->json([
            'message' => 'User registered successfully',
            'user'    => $user,
            'token'   => $plainTextToken,
            // 'expires_at' => $lastToken->expires_at->timestamp, // убрали
        ], 201);
    }

    /**
     * Логин пользователя.
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json(['message' => 'Invalid credentials'], 422);
        }

        $user = Auth::user();

        // Создаём Sanctum-токен
        $tokenResult = $user->createToken('authToken');
        $plainTextToken = $tokenResult->plainTextToken;

        // Устанавливаем срок действия (30 минут)
        $lastToken = $user->tokens()->latest('id')->first();
        $lastToken->expires_at = now()->addMinutes(30);
        $lastToken->save();

        // НЕ возвращаем expires_at
        return response()->json([
            'message' => 'Logged in successfully',
            'token'   => $plainTextToken,
            'user'    => [
                'id'    => $user->id,
                'name'  => $user->name,
                'email' => $user->email,
            ],
        ], 200);
    }

    /**
     * Логаут пользователя (удаляем текущий Bearer-токен).
     */
    public function logout(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['message' => 'Not authenticated'], 401);
        }

        $user->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logged out successfully'
        ], 200);
    }

    /**
     * Данные о текущем пользователе (маршрут защищён 'auth:sanctum').
     */
    public function me(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['message' => 'Not logged in'], 401);
        }

        return response()->json([
            'id'    => $user->id,
            'name'  => $user->name,
            'email' => $user->email,
        ], 200);
    }
}
