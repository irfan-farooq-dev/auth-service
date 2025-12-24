<?php

return [
    'secret' => env('JWT_SECRET'),
    'ttl' => env('JWT_TTL', 3600),
    'issuer' => env('JWT_ISSUER', 'auth_service'),
    'algo' => env('JWT_ALGORITHM', 'HS256'),
];

