<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Auth\LdapAuthenticator;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\LoginRequest;
use App\Http\Resources\UserResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

final class AuthController extends Controller
{
    public function __construct(private readonly LdapAuthenticator $ldapAuth) {}

    /**
     * Authenticate a user with username and password (against LDAP) and issue an API token.
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $user = $this->ldapAuth->checkPassword(
            $request->string('username')->toString(),
            $request->string('password')->toString(),
        );

        if ($user === null) {
            throw ValidationException::withMessages([
                'username' => [trans('auth.failed')],
            ]);
        }

        $deviceName = $request->string('device_name')->toString() ?: 'api';

        $token = $user->createToken($deviceName);

        return response()->json([
            'token' => $token->plainTextToken,
            'user' => new UserResource($user),
        ]);
    }

    /**
     * Revoke the token that was used to authenticate the current request.
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Déconnecté.']);
    }
}
