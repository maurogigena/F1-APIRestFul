<?php

namespace App\Http\Controllers\Api;

use App\Traits\ApiResponses;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\LoginUserRequest;
use App\Http\Requests\Api\RegisterUserRequest;
use App\Models\User;
use App\Permissions\Api\Abilities;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    use ApiResponses; 

    public function register(RegisterUserRequest $request)
    {
        // Si ya hay un usuario autenticado (con token), no puede registrar
        if (Auth::guard('sanctum')->check()) {
            return $this->error('Authenticated users cannot register a new account.', 403);
        }

        // Crear nuevo usuario NO admin
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'is_admin' => false,
        ]);

        return $this->ok('Welcome! User registered successfully', [
            'user' => $user,
            'token' => $user->createToken(
                'API token for ' . $user->email,
                Abilities::getAbilities($user)
            )->plainTextToken
        ]);
    }

    /**
     * Login
     * 
     */

    public function login(LoginUserRequest $request)
    {
        $this->ensureIsNotRateLimited($request);

        // $request->validate($request->all()); <- not necesary because now I'm using LoginUserRequest

        if (!Auth::attempt($request->only('email', 'password'))) {
            return $this->error('Invalid Credentials.', 401);
        }

        $user = User::firstWhere('email', $request->email);

        RateLimiter::clear($this->throttleKey($request));

        return $this->ok(
            'Authenticated', 
            [
                'token' => $user->createToken(
                'API token for ' . $user->email, 
                Abilities::getAbilities($user))->plainTextToken    
                // the expiration was modified in sanctum.php by default (expiration => 60*24*30)
            ]
        );
    }

    /**
     * Logout
     * 
     */
    public function logout(Request $request)
    {
        $authUser = $request->user();

        if (!$authUser) {
            return $this->error([
                [
                    'type' => 'AuthenticationException',
                    'status' => 401,
                    'message' => 'Unauthenticated.'
                ]
            ], 401);
        }

        $email = $request->input('email');
        $password = $request->input('password');

        // Buscar usuario al que se quiere hacer logout
        $userToLogout = User::where('email', $email)->first();

        if (!$userToLogout) {
            return $this->error([
                [
                    'type' => 'NotFoundException',
                    'status' => 404,
                    'message' => 'User to logout not found.'
                ]
            ], 404);
        }

        // Si no es admin, sólo puede desloguearse a sí mismo y debe validar password
        if (!$authUser->is_admin) {
            if ($authUser->id !== $userToLogout->id) {
                return $this->error([
                    [
                        'type' => 'AuthorizationException',
                        'status' => 403,
                        'message' => 'Unauthorized. You can only logout yourself.'
                    ]
                ], 403);
            }

            // Validar password
            if (!Hash::check($password, $authUser->password)) {
                return $this->error([
                    [
                        'type' => 'AuthorizationException',
                        'status' => 401,
                        'message' => 'Unauthorized. Password does not match.'
                    ]
                ], 401);
            }
        }

        // Ahora eliminar el token del usuario a desloguear
        // Aquí hay dos opciones:
        // 1) Eliminar solo el token actual del usuario autenticado (si es logout propio)
        // 2) Si es admin, eliminar todos los tokens del usuario a desloguear (logout forzado)

        if ($authUser->is_admin && $authUser->id !== $userToLogout->id) {
            // Logout forzado: eliminar todos los tokens del usuario objetivo
            $userToLogout->tokens()->delete();
        } else {
            // Logout normal: eliminar token actual (del usuario autenticado)
            $authUser->currentAccessToken()->delete();
        }

        return $this->ok('Logged out successfully.');
    }

    // SECURITY - RATE LIMITER
    protected function ensureIsNotRateLimited(Request $request)
    {
        if (RateLimiter::tooManyAttempts($this->throttleKey($request), 5)) {
            throw ValidationException::withMessages([
                'email' => ['Too many attempts. Try again in ' . RateLimiter::availableIn($this->throttleKey($request)) . ' segundos.'],
            ]);
        }

        RateLimiter::hit($this->throttleKey($request), 60);
    }

    protected function throttleKey(Request $request)
    {
        return Str::transliterate(Str::lower($request->input('email')));
    }

    // public function register() {
    //     return $this->ok('register');
    // }
}