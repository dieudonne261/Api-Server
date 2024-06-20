<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

/**
 * @OA\Info(title="API Contact", version="1.0")
 */
class UserController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/register",
     *     summary="Enregistrer un nouvel utilisateur",
     *     tags={"Users"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name","email","password","password_confirmation"},
     *             @OA\Property(property="name", type="string", example="Dede"),
     *             @OA\Property(property="email", type="string", format="email", example="dede@dede.com"),
     *             @OA\Property(property="password", type="string", format="password", example="dededede"),
     *             @OA\Property(property="password_confirmation", type="string", format="password", example="dededede")
     *         ),
     *     ),
     *     @OA\Response(response=200, description="Utilisateur enregistré avec succès"),
     *     @OA\Response(response=422, description="Erreur de validation")
     * )
     */
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        return response()->json(['message' => 'Utilisateur enregistré avec succès'], 201);
    }

    /**
     * @OA\Post(
     *     path="/api/login",
     *     summary="Connecter un utilisateur",
     *     tags={"Users"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email","password"},
     *             @OA\Property(property="email", type="string", format="email", example="dede@dede.com"),
     *             @OA\Property(property="password", type="string", format="password", example="dededede")
     *         ),
     *     ),
     *     @OA\Response(response=200, description="Token d'accès généré avec succès", @OA\JsonContent(
     *         @OA\Property(property="access_token", type="string"),
     *         @OA\Property(property="token_type", type="string", example="Bearer")
     *     )),
     *     @OA\Response(response=401, description="Non autorisé")
     * )
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        $credentials = $request->only('email', 'password');

        if (!Auth::attempt($credentials)) {
            throw ValidationException::withMessages([
                'email' => ['Les informations fournies sont incorrectes'],
            ]);
        }

        $user = $request->user();
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json(['access_token' => $token, 'token_type' => 'Bearer'], 200);
    }

    /**
     * @OA\Post(
     *     path="/api/logout",
     *     summary="Déconnecter un utilisateur",
     *     tags={"Users"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=200, description="Déconnexion réussie"),
     *     @OA\Response(response=401, description="Non autorisé")
     * )
     */
    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();
        return response()->json(['message' => 'Déconnexion réussie'], 200);
    }
}