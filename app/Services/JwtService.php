<?php

namespace App\Services;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class JwtService {
    public static function generateToken($userId) {

        if(!$userId) {
            throw new \InvalidArgumentException("User ID is required to generate JWT token.");
        }

        $payload = [
            'iss' => config('jwt.issuer', 'auth_service'),     // issuer
            'sub' => $userId,                               // subject (user id)
            'iat' => time(),                                // issued at
            'exp' => time() + config('jwt.ttl', 60)          // expiry
        ];

        $key =  (string)config('jwt.secret');

        return JWT::encode($payload, $key, config('jwt.algo', 'HS256'));
    }

    public static function decodeToken($token) {
        return JWT::decode($token, new Key(config('jwt.secret'), config('jwt.algo', 'HS256')));
    }
}
