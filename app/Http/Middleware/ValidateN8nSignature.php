<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Services\N8nService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ValidateN8nSignature
{
    public function handle(Request $request, Closure $next): Response
    {
        $signature = $request->header('X-N8N-Signature');

        if (empty($signature)) {
            return response()->json([
                'message' => 'Missing signature header.',
            ], Response::HTTP_UNAUTHORIZED);
        }

        $payload = $request->all();

        if (! N8nService::verifySignature($signature, $payload)) {
            return response()->json([
                'message' => 'Invalid signature.',
            ], Response::HTTP_UNAUTHORIZED);
        }

        return $next($request);
    }
}
