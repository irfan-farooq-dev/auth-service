<?php

/**
 * OpenAPI security definitions and global requirements.
 *
 * @OA\SecurityScheme(
 *     securityScheme="bearerAuth",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT"
 * )
 *
 * @OA\SecurityRequirement(
 *     security={{"bearerAuth":{}}}
 * )
 */
class OpenApiSecurity {}
