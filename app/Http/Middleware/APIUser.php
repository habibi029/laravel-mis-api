<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Http\Responses\ErrorResponse;
use Symfony\Component\HttpFoundation\Response;

class APIUser
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $bearer_token = $request->bearerToken() ?? null;
        if (empty($bearer_token)) {
            return new ErrorResponse(message: 'Forbidden access!', code: Response::HTTP_FORBIDDEN);
        }

        return $next($request);
    }
}
