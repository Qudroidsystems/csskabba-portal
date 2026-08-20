<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class DeviceAuth
{
    public function handle(Request $request, Closure $next): Response
    {
        $deviceKey = $request->header('X-Device-Key');

        if (!$deviceKey || !hash_equals(
            config('services.device.key'),
            $deviceKey
        )) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized device.',
            ], 401);
        }

        return $next($request);
    }
}