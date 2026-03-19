<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SoloReteAziendale
{
    /**
     * Range IP consentiti — modifica con il tuo range aziendale reale
     */
    private array $rangeConsentiti = [
        '192.168.3.',   // rete aziendale principale
        '127.0.0.1',    // localhost (per sviluppo)
        '::1',          // localhost IPv6
    ];

    public function handle(Request $request, Closure $next)
    {
        $ip = $request->ip();

        $autorizzato = collect($this->rangeConsentiti)->contains(function ($range) use ($ip) {
            return str_starts_with($ip, $range) || $ip === $range;
        });

        if (!$autorizzato) {
            abort(403, 'Accesso consentito solo dalla rete aziendale.');
        }

        return $next($request);
    }
}
