<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseController as BaseController;
use App\Http\Requests\Api\User\CreateUserRequest;
use App\Http\Requests\Api\User\LoginUserRequest;
use App\Services\UserService;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;

class AuthController extends BaseController
{
    protected $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    public function register(CreateUserRequest $request)
    {
        $user   = $this->userService->registerUser($request->validated());
        $token  = $user->createToken('auth_token')->plainTextToken;

        return $this->sendResponse([
            'user'        => new UserResource($user),
            'token'       => $token,
        ], 'Pendaftaran berhasil');
    }

    public function login(LoginUserRequest $request)
    {
        $user   = $this->userService->loginUser($request->email, $request->password);
        $token  = $user->createToken('auth_token')->plainTextToken;

        return $this->sendResponse([
            'user'          => new UserResource($user),
            'token'         => $token,
            'token_type'    => 'Bearer',
        ], 'Login berhasil');
    }

    public function logout(Request $request)
    {
        $this->userService->logoutUser($request->user());

        return $this->sendResponse(
            null,
            'Logout berhasil'
        );
    }
}
