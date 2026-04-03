<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\RequestLog;

class RequestLogMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $startTime = microtime(true);

        $response = $next($request);

        $processing_time = floor((microtime(true) - $startTime) * 1000);

        RequestLog::create([
            'user_id' => auth()->id(),
            'method'  => $request->method(),
            'path'    => $request->path(),
            'payload' => $request->except(['password', 'password_confirmation']),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'status_code' => $response->getStatusCode(),
            'processing_time' => $processing_time,
            'referer' => $request->headers->get('referer')
        ]);

        return $response;
    }
}
