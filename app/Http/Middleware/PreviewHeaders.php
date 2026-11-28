<?php
namespace App\Http\Middleware;
use Closure;
use Illuminate\Http\Request;
class PreviewHeaders {
    public function handle(Request $request, Closure $next) {
        if(app()->environment('production')&&$request->is('admin*','staff*','login*','logout')&&!$request->isSecure())return response('HTTPS required for staff access',403,['Cache-Control'=>'private, no-store']);
        if(config('verdleaf.preview')&&app()->environment('production')){
            $key=(string)config('verdleaf.preview_key');$user=(string)config('verdleaf.preview_user');
            if(strlen($key)<24||!hash_equals($user,(string)$request->getUser())||!hash_equals($key,(string)$request->getPassword()))return response('Protected staging',401,['WWW-Authenticate'=>'Basic realm="Verdleaf staging"','X-Robots-Tag'=>'noindex, nofollow','Cache-Control'=>'private, no-store']);
        }
        $response = $next($request);
        if(!config('verdleaf.preview')&&$request->isMethod('GET')&&$response->getStatusCode()===200){
            $event=match(true){$request->routeIs('home','ar.home')=>'homepage_view',$request->routeIs('product','ar.product')=>'product_view',$request->routeIs('products','ar.products','atlas','finder')=>'catalogue_view',$request->routeIs('desk','ar.desk')=>'buyer_desk_view',default=>null};
            if($event)try{\App\Services\Metrics::increment($event);}catch(\Throwable $e){report($e);}
        }
        if (config('verdleaf.preview') || $request->is('admin*','staff*','buyer-desk*','ar/buyer-desk*','ar/compare*','ar/enquire*','login*','compare*','shortlist*','shortlist*')) {
            $response->headers->set('X-Robots-Tag','noindex, nofollow');
        }
        if ($request->is('admin*','staff*','buyer-desk*','ar/buyer-desk*','ar/compare*','ar/enquire*','login*','compare*')) $response->headers->set('Cache-Control','private, no-store');
        if(!config('verdleaf.preview')&&($request->is('enquire/*','atlas','ingredient-finder','shortlist*','catalogue/print')||(($request->is('products','ar/products'))&&$request->query())))$response->headers->set('X-Robots-Tag','noindex, follow');
        $response->headers->set('X-Content-Type-Options','nosniff');
        $response->headers->set('Referrer-Policy','strict-origin-when-cross-origin');
        $response->headers->set('X-Frame-Options','DENY');
        $response->headers->set('Permissions-Policy','camera=(), microphone=(), geolocation=()');
        if($request->is('admin*','staff*','login*')){
            $response->headers->set('Content-Security-Policy',"default-src 'self'; script-src 'self'; style-src 'self'; img-src 'self' data:; media-src 'self'; connect-src 'self'; object-src 'none'; frame-ancestors 'none'; base-uri 'none'; form-action 'self'");
            $response->headers->set('Referrer-Policy','no-referrer');
            $response->headers->set('Cache-Control','private, no-store');
        }
        if(app()->environment('production')&&$request->isSecure())$response->headers->set('Strict-Transport-Security','max-age=31536000');
        return $response;
    }
}
