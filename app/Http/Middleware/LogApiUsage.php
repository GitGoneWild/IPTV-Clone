<?php

namespace App\Http\Middleware;

use App\Models\ApiUsageLog;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class LogApiUsage
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $startTime = microtime(true);

        $response = $next($request);

        $endTime = microtime(true);
        $responseTimeMs = (int) (($endTime - $startTime) * 1000);

        // Log the API usage asynchronously
        ApiUsageLog::create([
            'user_id' => $request->user()?->id,
            'endpoint' => $request->path(),
            'method' => $request->method(),
            'ip_address' => $request->ip(),
            'response_status' => $response->getStatusCode(),
            'response_time_ms' => $responseTimeMs,
            'user_agent' => $request->userAgent(),
        ]);

        return $response;
    }
}
