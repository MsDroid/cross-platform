<?php

namespace App\Http\Controllers\Api\V1\Profile;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DeviceController extends Controller
{
    public function store(RegisterDeviceRequest $request, DeviceService $service)
    {
        $device = $service->register($request->user(), $request->validated());

        return response()->json(['device_id' => $device->id], 201);
    }

    public function destroy(Request $request, DeviceService $service)
    {
        $request->validate(['fcm_token' => 'required|string']);
        $service->unregister($request->user(), $request->fcm_token);

        return response()->json(['message' => 'Device removed.']);
    }
}
