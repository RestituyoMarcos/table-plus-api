<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /**
    * @OA\Post(
    * path="/api/register",
    * summary="Registra un nuevo usuario",
    * tags={"Authentication"},
    * @OA\RequestBody(
    * required=true,
    * @OA\JsonContent(
    * required={"name","email","password","password_confirmation"},
    * @OA\Property(property="name", type="string", example="John Doe"),
    * @OA\Property(property="email", type="string", format="email", example="john@example.com"),
    * @OA\Property(property="password", type="string", format="password", example="password123"),
    * @OA\Property(property="password_confirmation", type="string", format="password", example="password123")
    * )
    * ),
    * @OA\Response(
    * response=201,
    * description="User registered successfully",
    * @OA\JsonContent(
    * @OA\Property(property="message", type="string"),
    * @OA\Property(property="user", type="object"),
    * @OA\Property(property="access_token", type="string"),
    * @OA\Property(property="token_type", type="string", example="Bearer")
    * )
    * ),
    * @OA\Response(
    * response=422,
    * description="Validation error"
    * )
    * )
    */
    public function register(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $data['password'] = Hash::make($data['password']);

        $user = User::create($data);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json(['message' => '¡Usuario registrado correctamente!', 'user' => $user, 'access_token' => $token, 'token_type' => 'Bearer'], 201);
    }

    /**
    * @OA\Post(
    * path="/api/login",
    * summary="Loguea un usuario existente",
    * tags={"Authentication"},
    * @OA\RequestBody(
    * required=true,
    * @OA\JsonContent(
    * required={"email","password"},
    * @OA\Property(property="email", type="string", format="email", example="john@example.com"),
    * @OA\Property(property="password", type="string", format="password", example="password123")
    * )
    * ),
    * @OA\Response(
    * response=200,
    * description="Login successful",
    * @OA\JsonContent(
    * @OA\Property(property="message", type="string"),
    * @OA\Property(property="user", type="object"),
    * @OA\Property(property="access_token", type="string"),
    * @OA\Property(property="token_type", type="string", example="Bearer")
    * )
    * ),
    * @OA\Response(
    * response=401,
    * description="Invalid credentials"
    * )
    * )
    */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        if (!auth()->attempt($credentials)) {
            return response()->json(['message' => 'Credenciales inválidas'], 401);
        }

        $user = auth()->user();
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json(['message' => '¡Inicio de sesión exitoso!', 'user' => $user, 'access_token' => $token, 'token_type' => 'Bearer']);
    }

    /**
    * @OA\Post(
    * path="/api/logout",
    * summary="Desloguea al usuario",
    * tags={"Authentication"},
    * security={{"bearerAuth":{}}},
    * @OA\Response(
    * response=200,
    * description="Successfully logged out",
    * @OA\JsonContent(
    * @OA\Property(property="message", type="string")
    * )
    * ),
    * @OA\Response(
    * response=401,
    * description="Unauthenticated"
    * )
    * )
    */
    public function logout()
    {
        $user = auth()->user();
        $user->tokens()->delete();

        return response()->json(['message' => '¡Sesión cerrada correctamente!']);
    }
}
