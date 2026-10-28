<?php

namespace App\Http\Controllers\Api\V1\Profile;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function show(Request $request)
    {
        return new UserResource($request->user());
    }

    public function update(UpdateProfileRequest $request, ProfileService $service)
    {
        $user = $service->update($request->user(), $request->validated());

        return new UserResource($user);
    }

    public function updatePassword(UpdatePasswordRequest $request, ProfileService $service)
    {
        $service->updatePassword($request->user(), $request->validated()['password']);

        return response()->json(['message' => 'Password updated.']);
    }

    public function destroy(Request $request)
    {
        $request->user()->tokens()->delete();
        $request->user()->delete(); // soft delete

        return response()->json(['message' => 'Account scheduled for deletion.']);
    }
}
