<?php

namespace App\Http\Controllers;

use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Services\AuthService;

class AuthController extends Controller
{
    /**
     * The service class handling authentication logic.
     *
     * @var \App\Services\AuthService
     */
    protected $authService;

    /**
     * Inject AuthService to handle authentication-related logic.
     *
     * @param \App\Services\AuthService $authService
     */
    public function __construct(AuthService $authService)
    {
        // Assign AuthService instance to the controller
        $this->authService = $authService;
    }

    /**
     * Register a new user.
     *
     * @OA\Post(
     *     path="/api/auth/register",
     *     tags={"Auth"},
     *     summary="Register a new user",
     *     description="Registers a new user and returns a user object with a token",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name","email","password","password_confirmation"},
     *             @OA\Property(property="name", type="string", example="John Doe"),
     *             @OA\Property(property="email", type="string", format="email", example="user@example.com"),
     *             @OA\Property(property="password", type="string", format="password", example="password123"),
     *             @OA\Property(property="password_confirmation", type="string", format="password", example="password123"),
     *             @OA\Property(property="fcm_token", type="string", example="example_fcm_token")
     *         ),
     *     ),
     *     @OA\Response(response=201, description="User registered successfully"),
     *     @OA\Response(response=400, description="Bad request"),
     *     @OA\Response(response=422, description="Validation error")
     * )
     *
     * @param \App\Http\Requests\Auth\RegisterRequest $request Validated registration data
     * @return \Illuminate\Http\JsonResponse JSON response containing user data and token
     */
    public function register(RegisterRequest $request)
    {
        // Use AuthService to handle registration logic
        // The request data is filtered to include only necessary fields
        $result = $this->authService->register($request->only('name', 'email', 'password'));

        // Return a success response with user details and authentication token
        return $this->success(
            [
                'user' => $result['user'],
                'token' => $result['token']
            ],
            'User registered successfully'
        );
    }

    /**
     * Login an existing user.
     *
     * @OA\Post(
     *     path="/api/auth/login",
     *     tags={"Auth"},
     *     summary="Login an existing user",
     *     description="Authenticates a user and returns a user object with a token",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email","password"},
     *             @OA\Property(property="email", type="string", format="email", example="user@example.com"),
     *             @OA\Property(property="password", type="string", format="password", example="password123")
     *         ),
     *     ),
     *     @OA\Response(response=200, description="User logged in successfully"),
     *     @OA\Response(response=401, description="Invalid credentials"),
     *     @OA\Response(response=422, description="Validation error")
     * )
     *
     * @param \App\Http\Requests\Auth\LoginRequest $request Validated login data
     * @return \Illuminate\Http\JsonResponse JSON response containing user data and token, or an error response
     */
    public function login(LoginRequest $request)
    {
        // Use AuthService to handle login logic
        // The request data is filtered to include only email and password
        $result = $this->authService->login($request->only('email', 'password'));

        // If login fails, return an error response with HTTP 401 status
        if (!$result) {
            return $this->error('Invalid credentials', 401);
        }

        // Return a success response with user details and authentication token
        return $this->success(
            [
                'user' => $result['user'],
                'token' => $result['token']
            ],
            'User logged in successfully'
        );
    }

    /**
     * Logout the authenticated user.
     *
     * @OA\Post(
     *     path="/api/auth/logout",
     *     tags={"Auth"},
     *     summary="Logout the authenticated user",
     *     description="Revokes the user's token and logs them out",
     *     security={{"Bearer":{}}},
     *     @OA\Response(response=200, description="User logged out successfully"),
     *     @OA\Response(response=401, description="Unauthenticated")
     * )
     *
     * @return \Illuminate\Http\JsonResponse JSON response confirming successful logout
     */
    public function logout()
    {
        // Use AuthService to handle logout by revoking user's tokens
        $this->authService->logout();

        // Return a success response indicating the user has been logged out
        return $this->success(null, 'User logged out successfully');
    }
}
