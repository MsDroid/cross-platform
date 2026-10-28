<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class LogoutController extends Controller
{
    public function __invoke(Request $request, AuthService $auth)
    {
        $auth->logout($request->user(), $request->user()->currentAccessToken()?->id);

        return response()->json(['message' => 'Logged out.']);
    }
}
