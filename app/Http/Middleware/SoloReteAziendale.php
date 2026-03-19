<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SoloReteAziendale
{
    public function handle(Request $request, Closure $next)
    {
        $ip = $request->ip();

        if (!$this->isPrivate($ip)) {
            abort(403, 'Accesso consentito solo dalla rete aziendale.');
        }

        return $next($request);
    }

    private function isPrivate(string $ip): bool
    {
        // Localhost
        if ($ip === '127.0.0.1' || $ip === '::1') {
            return true;
        }

        // RFC 1918: qualsiasi IP privato = rete aziendale o VPN
        return filter_var(
            $ip,
            FILTER_VALIDATE_IP,
            FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE
        ) === false;
    }
}
