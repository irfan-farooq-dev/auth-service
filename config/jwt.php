<?php

return [
    'private_key_path' => storage_path(env('AUTH_PRIVATE_KEY_PATH', 'keys/id_rsa.pem')),
    'public_key_path'  => storage_path(env('AUTH_PUBLIC_KEY_PATH', 'keys/id_rsa.pub')),
    'secret'           => env('JWT_SECRET'),
    'ttl'              => env('JWT_TTL', 3600),
    'issuer'           => env('JWT_ISSUER', 'auth_service'),
    'algo'             => env('JWT_ALGORITHM', 'RS256'),
];