<?php
namespace App\Http\Middleware;
use Closure;
use Illuminate\Http\Request;
class PreviewHeaders {
    public function handle(Request $request, Closure $next) {
        $response = $next($request);
        if (config('verdleaf.preview') || $request->is('admin*','buyer-desk*','login','compare*')) {
            $response->headers->set('X-Robots-Tag','noindex, nofollow');
        }
        if ($request->is('admin*','buyer-desk*','login','compare*')) $response->headers->set('Cache-Control','private, no-store');
        if($request->is('enquire/*','atlas','ingredient-finder')||(($request->is('products','ar/products'))&&$request->query()))$response->headers->set('X-Robots-Tag','noindex, follow');
        $response->headers->set('X-Content-Type-Options','nosniff');
        $response->headers->set('Referrer-Policy','strict-origin-when-cross-origin');
        return $response;
    }
}
