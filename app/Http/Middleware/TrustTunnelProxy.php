<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Symfony\Component\HttpFoundation\Response;

class TrustTunnelProxy
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($this->isTunnelRequest($request)) {
            $root = 'https://'.$request->getHost();

            URL::forceScheme('https');
            URL::forceRootUrl($root);

            config([
                'app.url' => $root,
                'session.secure' => true,
                'session.same_site' => 'lax',
                'session.domain' => null,
            ]);
        }

        return $next($request);
    }

    private function isTunnelRequest(Request $request): bool
    {
        $host = strtolower($request->getHost());

        if (str_ends_with($host, '.trycloudflare.com')) {
            return true;
        }

        if ($request->headers->get('CF-Visitor') !== null) {
            return true;
        }

        return $request->header('X-Forwarded-Proto') === 'https'
            && ! in_array($host, ['127.0.0.1', 'localhost'], true);
    }
}
