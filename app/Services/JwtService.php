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

        $key = file_get_contents(config('jwt.private_key_path'));

        return JWT::encode($payload, $key, config('jwt.algo', 'RS256'));
    }

    public static function decodeToken($token)
    {
        $publicKey = file_get_contents(config('jwt.public_key_path'));

        \Log::info('PublicKey: ' . $publicKey);

        // Detect OpenSSH public key format (starts with "ssh-rsa") which is not a PEM
        // format expected by Firebase\JWT for RS256 verification.
        if (strpos(trim($publicKey), 'ssh-rsa') === 0) {
            throw new \RuntimeException("Public key appears to be OpenSSH format (starts with 'ssh-rsa'). Convert it to PEM (e.g. `ssh-keygen -f id_rsa -e -m PEM > id_rsa_pub.pem` or `openssl rsa -in id_rsa -pubout -out id_rsa_pub.pem`) and update AUTH_PUBLIC_KEY_PATH to point to the PEM file.");
        }

        return JWT::decode($token, new Key($publicKey, config('jwt.algo', 'RS256')));
    }
}