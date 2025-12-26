<?php
namespace App\Services;

use App\Models\User;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class JwtService
{
    public static function generateToken($userId)
    {

        if (! $userId) {
            throw new \InvalidArgumentException("User ID is required to generate JWT token.");
        }

        $user = User::findOrFail($userId);

        $payload = [
            'iss'         => config('jwt.issuer', 'auth_service'),
            'sub'         => $user->id,
            'role'        => $user->roles->pluck('name'), // array of roles
            'permissions' => $user->roles->flatMap->permissions->pluck('name')->unique(),
            'iat'         => time(),
            'exp'         => time() + config('jwt.ttl', 3600),
        ];

        $key = (string) config('jwt.secret');

        return JWT::encode($payload, $key, config('jwt.algo', 'HS256'));
    }

    public static function decodeToken($token)
    {
        return JWT::decode($token, new Key(config('jwt.secret'), config('jwt.algo', 'HS256')));
    }
}
