<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UserRegisterRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

// Вместо этого есть laravel/sanctum, но в данном случае не критично
class AuthController extends Controller
{
    public function register(UserRegisterRequest $request)
    {
		// Плохо так делать, т.к. можно лишнего передать и будет 500
		// Правильно: $data = $request->validated();
        $request['password'] = bcrypt($request['password']);
        $request['remember_token'] = Str::random(10);
        $user = User::create($request->toArray());

        return response()->json([
            'status' => true,
            'message' => 'User created successfully',
            'token' => $user->createToken('auth_token')->plainTextToken,
        ], 201);
    }

    public function login(Request $request)
    {
		// Нет валидации запроса
        if (Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
            $user = Auth::user();
            return response()->json([
                'status' => true,
                'message' => 'User created successfully',
                'token' => $user->createToken('auth_token')->plainTextToken,
            ], 201);
        } else {
            return response()->json([
                'status' => false,
                'message' => 'Incorrect email or password',
            ], 401);
        }
    }
}
