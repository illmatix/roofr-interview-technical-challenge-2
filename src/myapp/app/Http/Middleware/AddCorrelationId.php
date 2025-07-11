<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class AddCorrelationId
{
    /*
     * Handles Linking related logs (API Request > allocation > DB writes with a request based UUID
     *
     */
    public function handle($request, Closure $next)
    {
        $id = Str::uuid()->toString();
        Log::withContext(['correlation_id' => $id]);
        return $next($request);
    }
}
