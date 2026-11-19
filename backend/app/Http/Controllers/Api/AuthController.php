<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Throwable;

class AuthController extends Controller
{
    /**
     * Register a new user and generate a Sanctum token.
     */
    public function register(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            Log::warning('Validasi registrasi pengguna gagal', [
                'user' => 'guest',
                'aksi' => 'register_user',
                'sebab' => 'Field input tidak valid: ' . implode(', ', array_keys($validator->errors()->toArray())),
                'invalid_fields' => array_keys($validator->errors()->toArray()),
            ]);

            return response()->json([
                'message' => 'The given data was invalid.',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $validated = $validator->validated();

            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
            ]);

            $token = $user->createToken('auth_token')->plainTextToken;

            Log::info('Pengguna baru berhasil didaftarkan', [
                'user' => $user->id,
                'aksi' => 'register_user',
                'sebab' => 'Data pengguna berhasil disimpan dan token autentikasi diterbitkan',
            ]);

            return response()->json([
                'message' => 'User registered successfully',
                'access_token' => $token,
                'token_type' => 'Bearer',
                'user' => $user,
            ], 201);
        } catch (Throwable $e) {
            Log::error('Gagal memproses registrasi pengguna ke basis data', [
                'user' => 'guest',
                'aksi' => 'register_user',
                'sebab' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'Terjadi kesalahan sistem saat mendaftarkan akun.',
            ], 500);
        }
    }

    /**
     * Log in user and generate a Sanctum token.
     */
    public function login(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            Log::warning('Validasi login pengguna gagal', [
                'user' => 'guest',
                'aksi' => 'login_user',
                'sebab' => 'Field input tidak valid: ' . implode(', ', array_keys($validator->errors()->toArray())),
                'invalid_fields' => array_keys($validator->errors()->toArray()),
            ]);

            return response()->json([
                'message' => 'The given data was invalid.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $credentials = $validator->validated();

        if (!Auth::attempt($credentials)) {
            Log::warning('Autentikasi pengguna gagal', [
                'user' => 'guest',
                'aksi' => 'login_user',
                'sebab' => 'Kombinasi email dan kata sandi tidak cocok',
            ]);

            return response()->json([
                'message' => 'Invalid credentials',
            ], 401);
        }

        try {
            $user = Auth::user();
            $token = $user->createToken('auth_token')->plainTextToken;

            Log::info('Pengguna berhasil login', [
                'user' => $user->id,
                'aksi' => 'login_user',
                'sebab' => 'Autentikasi kredensial berhasil diverifikasi',
            ]);

            return response()->json([
                'message' => 'Login successful',
                'access_token' => $token,
                'token_type' => 'Bearer',
                'user' => $user,
            ], 200);
        } catch (Throwable $e) {
            Log::error('Gagal menerbitkan token autentikasi pengguna', [
                'user' => Auth::id() ?? 'guest',
                'aksi' => 'login_user',
                'sebab' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'Terjadi kesalahan sistem saat proses autentikasi.',
            ], 500);
        }
    }

    /**
     * Log out user by revoking the current access token.
     */
    public function logout(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $userId = $user ? $user->id : 'guest';
            $user->currentAccessToken()->delete();

            Log::info('Pengguna berhasil logout', [
                'user' => $userId,
                'aksi' => 'logout_user',
                'sebab' => 'Token akses Sanctum berhasil dicabut (revoked)',
            ]);

            return response()->json([
                'message' => 'Logged out successfully',
            ], 200);
        } catch (Throwable $e) {
            Log::error('Gagal memproses logout pengguna', [
                'user' => $request->user()?->id ?? 'guest',
                'aksi' => 'logout_user',
                'sebab' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'Terjadi kesalahan sistem saat logout.',
            ], 500);
        }
    }
}
