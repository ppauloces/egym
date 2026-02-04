<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::with('academia')->where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['As credenciais informadas estão incorretas.'],
            ]);
        }

        if (! $user->ativo) {
            throw ValidationException::withMessages([
                'email' => ['Sua conta está desativada. Entre em contato com o administrador.'],
            ]);
        }

        if ($user->isGestor() && (! $user->academia || ! $user->academia->ativo)) {
            throw ValidationException::withMessages([
                'email' => ['A academia vinculada à sua conta está desativada.'],
            ]);
        }

        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
            ],
            'academia' => $user->academia ? [
                'id' => $user->academia->id,
                'nome' => $user->academia->nome,
                'logo' => $user->academia->logo ? asset('storage/' . $user->academia->logo) : null,
                'cor_primaria' => $user->academia->cor_primaria,
                'cor_secundaria' => $user->academia->cor_secundaria,
            ] : null,
            'token' => $token,
        ]);
    }

    public function me(Request $request): JsonResponse
    {
        $user = $request->user()->load('academia');

        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
            ],
            'academia' => $user->academia ? [
                'id' => $user->academia->id,
                'nome' => $user->academia->nome,
                'logo' => $user->academia->logo ? asset('storage/' . $user->academia->logo) : null,
                'cor_primaria' => $user->academia->cor_primaria,
                'cor_secundaria' => $user->academia->cor_secundaria,
            ] : null,
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logout realizado com sucesso.']);
    }
}
