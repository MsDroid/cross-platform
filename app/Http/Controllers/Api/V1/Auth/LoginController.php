<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;

class LoginController extends Controller
{
    public function __invoke(LoginRequest $request, AuthService $auth)
    {
        $result = $auth->login($request->validated());

        return response()->json([
            'user' => new UserResource($result['user']),
            'token' => $result['token'],
        ]);
    }
}
