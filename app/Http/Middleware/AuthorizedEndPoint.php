<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthorizedEndPoint
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $listEndPoints = array('api/sample_1'); // Membaca Database atau global configuration

        $currentRoute = $request->path();

        if (!in_array($currentRoute, $listEndPoints)) {
            return response()->json([
                'message' => 'Access to this endpoint is not permitted'
            ], Response::HTTP_FORBIDDEN);
        }

        return $next($request);
    }
}
