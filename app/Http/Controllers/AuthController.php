<?php

namespace App\Http\Controllers;

use App\Models\User;
use Dedoc\Scramble\Attributes\Group;
use Hash;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AuthController extends Controller
{
    #[Group('Public - Authentication', 'Endpoint autentikasi yang bisa diakses sebelum login.', 1)]
    public function register(Request $request)
    {
        $fields = $request->validate([
            'name' => 'required|string',
            'email' => 'required|string|unique:users,email',
            'password' => 'required|string|confirmed',
            'region_code' => [
                'required',
                'string', 
                Rule::exists('region', 'code')
                    ->where(fn ($query) => $query->where('level', 4)),
                ],
        ]);

        $user = User::create([
            'name' => $fields['name'],
            'email' => $fields['email'],
            'password' => bcrypt($fields['password']),
            'region_code' => $fields['region_code'],
            'role' => 'user'
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;
        return response([
            'user' => $user,
            'token' => $token
        ], 201);
    }

    #[Group('Public - Authentication', 'Endpoint autentikasi yang bisa diakses sebelum login.', 1)]
    public function login(Request $request)
    {
        $fields = $request->validate([
            'email' => 'required|string',
            'password' => 'required|string'
        ]);

        $user = User::where('email', $fields['email'])->first();
        if (!$user || !Hash::check($fields['password'], $user->password)) {
            return response(['message' => 'Bad credentials'], 401);
        }
        $token = $user->createToken('auth_token')->plainTextToken;
        return response([
            'user' => $user,
            'token' => $token
        ], 200);
    }

    #[Group('User - Authentication', 'Endpoint autentikasi untuk user yang sudah login.', 8)]
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response([
            'message' => 'Logged out successfully'
        ]);
    }
}
