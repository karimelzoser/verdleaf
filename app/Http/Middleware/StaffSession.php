<?php
namespace App\Http\Middleware;
use App\Services\StaffSecurity;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
class StaffSession {
 public function handle(Request $r,Closure $next){
  $u=$r->user();abort_unless($u,401);
  if($u->disabled_at||!in_array($u->role,['admin','editor','quality','sales','analytics'],true))return $this->end($r);
  if(StaffSecurity::required()){
   $now=time();$verified=$r->session()->get('staff_verified');$last=$r->session()->get('staff_last');$started=$r->session()->get('staff_started');$version=$r->session()->get('staff_version');
   if($verified!==$u->id||$version!==(int)$u->session_version||!is_int($last)||!is_int($started)||$now-$last>900||$now-$started>28800)return $this->end($r);
   $r->session()->put('staff_last',$now);
  }
  $response=$next($r);$response->headers->set('Cache-Control','private, no-store');return $response;
 }
 private function end(Request $r){Auth::logout();$r->session()->invalidate();$r->session()->regenerateToken();return redirect()->route('login');}
}