<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\RefreshToken;
use App\Models\User;
use App\Services\JwtService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{

    /**
     * @OA\Post(
     *     path="/api/register",
     *     tags={"Auth"},
     *     summary="Register a new user",
     *     description="Create a new user and return a JWT token.",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name","email","password"},
     *             @OA\Property(property="name", type="string", example="Jane Doe"),
     *             @OA\Property(property="email", type="string", format="email", example="jane@example.com"),
     *             @OA\Property(property="password", type="string", format="password", example="secretPassword"),
     *             @OA\Property(property="password_confirmation", type="string", format="password", example="secretPassword")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful registration",
     *         @OA\JsonContent(
     *             @OA\Property(property="token", type="string", example="eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...")
     *         )
     *     ),
     *     @OA\Response(response=422, description="Validation error"),
     *     @OA\Response(response=409, description="User already exists")
     * )
     */
    public function register(Request $request)
    {

        $validatedData = $request->validate([
            'name'     => 'required',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|confirmed|min:6',
        ]);

        DB::beginTransaction();

        try {

            $user  = User::create($validatedData);
            $token = JwtService::generateToken($user->id);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();

            throw $e;
        }

        return response()->json(['user' => $user, 'token' => $token]);
    }

    /**
     * @OA\Post(
     *     path="/api/login",
     *     tags={"Auth"},
     *     summary="Authenticate a user",
     *     description="Validate credentials and return a JWT token and user object.",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email","password"},
     *             @OA\Property(property="email", type="string", format="email", example="jane@example.com"),
     *             @OA\Property(property="password", type="string", format="password", example="secretPassword")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful authentication",
     *         @OA\JsonContent(
     *             @OA\Property(property="user", type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="Jane Doe"),
     *                 @OA\Property(property="email", type="string", format="email", example="jane@example.com")
     *             ),
     *             @OA\Property(property="token", type="string", example="eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Invalid credentials"),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
    public function login(Request $request)
    {
        // Validate input
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string|min:6',
        ]);

        // Find user
        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            return response()->json(['error' => 'Invalid credentials'], 401);
        }

        // Short-lived access token (JWT)
        $accessToken = JwtService::generateToken($user->id);

        // Long-lived refresh token
        $refreshToken = bin2hex(random_bytes(32));
        RefreshToken::create([
            'user_id'    => $user->id,
            'token_hash' => hash('sha256', $refreshToken),
            'expires_at' => now()->addDays(14), // 2 weeks validity
        ]);

        // Roles and Permissions already included in JWT token (accessToken)
        // return response()->json([
        //     'user'          => $user,
        //     'access_token'  => $accessToken,
        //     'refresh_token' => $refreshToken,
        // ]);

        // Roles and Permissions added for testing purpose
        return response()->json([
            'user'          => $user,
            'access_token'  => $accessToken,
            'refresh_token' => $refreshToken,
            'role'          => $user->roles->pluck('name'), // array of roles
            'permissions'   => $user->roles->flatMap->permissions->pluck('name')->unique(),
        ]);

    }

    /**
     * @OA\Post(
     *     path="/api/refresh",
     *     tags={"Auth"},
     *     summary="Refresh access token",
     *     description="Exchange a valid refresh token for a new access token and rotate the refresh token.",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"refresh_token"},
     *             @OA\Property(property="refresh_token", type="string", example="a1b2c3..."),
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Tokens rotated and new access token returned",
     *         @OA\JsonContent(
     *             @OA\Property(property="access_token", type="string", example="ey..."),
     *             @OA\Property(property="refresh_token", type="string", example="newrefreshtoken...")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Invalid or expired refresh token")
     * )
     */
    public function refresh(Request $request)
    {
        $request->validate([
            'refresh_token' => 'required|string',
        ]);

        $provided = $request->refresh_token;
        $record   = RefreshToken::where('token_hash', hash('sha256', $provided))
            ->whereNull('revoked_at')
            ->where('expires_at', '>', now())
            ->first();

        if (! $record) {
            return response()->json(['error' => 'Invalid or expired refresh token'], 401);
        }

        // Issue new access token
        $accessToken = JwtService::generateToken($record->user_id);

        // Rotate refresh token (invalidate old one)
        $record->update(['revoked_at' => now()]);
        $newRefresh = bin2hex(random_bytes(32));
        RefreshToken::create([
            'user_id'    => $record->user_id,
            'token_hash' => hash('sha256', $newRefresh),
            'expires_at' => now()->addDays(14),
        ]);

        return response()->json([
            'access_token'  => $accessToken,
            'refresh_token' => $newRefresh,
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/logout",
     *     tags={"Auth"},
     *     summary="Logout user",
     *     description="Revoke all refresh tokens for the authenticated user.",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Logged out",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Logged out")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function logout(Request $request)
    {
        if (! $request->auth) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // revoke all refresh tokens for this user
        RefreshToken::where('user_id', $request->auth->sub)
            ->whereNull('revoked_at')
            ->update(['revoked_at' => now()]);

        return response()->json(['message' => 'Logged out']);
    }

}
