<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $user = User::create([
            'name' => $data['name'],
            'nickname' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role' => 'USER',
            'status' => 'ACTIVE',
            'points' => 500,
        ]);

        $token = $user->createToken('spa')->plainTextToken;
        AuditLog::record($user->id, 'AUTH_REGISTER', 'user', (string) $user->id);

        return response()->json(['token' => $token, 'user' => $user->toPublicArray()], 201);
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (! Auth::attempt($credentials)) {
            throw ValidationException::withMessages([
                'email' => ['Email hoặc mật khẩu không đúng.'],
            ]);
        }

        /** @var User $user */
        $user = Auth::user();
        if ($user->status !== 'ACTIVE') {
            Auth::logout();

            return response()->json(['message' => 'Tài khoản bị khóa'], 403);
        }

        $token = $user->createToken('spa')->plainTextToken;
        AuditLog::record($user->id, 'AUTH_LOGIN', 'user', (string) $user->id);

        return response()->json(['token' => $token, 'user' => $user->toPublicArray()]);
    }

    public function me(Request $request)
    {
        return response()->json(['user' => $request->user()->toPublicArray()]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()?->delete();

        return response()->json(['ok' => true]);
    }
}
