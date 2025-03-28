<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Mail\EmailVerificationMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;

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

        // Створюємо користувача з email_verified = false + токен
        $user = User::create([
            'name' => $request->input('name'),
            'email' => $request->input('email'),
            'password' => Hash::make($request->input('password')),
            'email_verified' => false,
            'email_verification_token' => Str::random(64),
        ]);

        // Надсилаємо листа для підтвердження email
        Mail::to($user->email)->send(new EmailVerificationMail($user->email_verification_token));

        return response()->json([
            'message' => 'User registered successfully. Please check your email to verify your account.',
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

        // 🔐 Проверка подтверждения email
        if (!$user->email_verified) {
            return response()->json([
                'message' => 'Please verify your email before logging in.'
            ], 403);
        }

        // Создаём Sanctum-токен
        $tokenResult = $user->createToken('authToken');
        $plainTextToken = $tokenResult->plainTextToken;

        // Устанавливаем срок действия (30 минут)
        $lastToken = $user->tokens()->latest('id')->first();
        $lastToken->expires_at = now()->addMinutes(30);
        $lastToken->save();

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

    public function verifyEmail(Request $request)
    {
        $token = $request->query('token');

        if (!$token) {
            return response()->json(['message' => 'Token is required'], 400);
        }

        $user = User::where('email_verification_token', $token)->first();

        if (!$user) {
            return response()->json(['message' => 'Invalid or expired token'], 404);
        }

        $user->email_verified = true;
        $user->email_verification_token = null;
        $user->save();

        return redirect('http://localhost:9000/#/verified');
    }

    public function resendVerification(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        if ($user->email_verified) {
            return response()->json(['message' => 'Email already verified'], 400);
        }

        // Генеруємо новий токен (за бажанням, або залишаємо старий)
        $user->email_verification_token = Str::random(64);
        $user->save();

        // Надсилаємо лист
        Mail::to($user->email)->send(new EmailVerificationMail($user->email_verification_token));

        return response()->json(['message' => 'Verification email resent']);
    }
}
