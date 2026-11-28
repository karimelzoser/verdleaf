<?php
namespace App\Http\Controllers;
use App\Models\User;
use App\Services\StaffSecurity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Auth,Hash,RateLimiter};
class StaffAuth {
 public function login(Request $r){if($r->user())return redirect()->route($r->user()->role==='admin'?'admin':'staff');return view('login');}
 private function key(Request $r,string $email):string{return 'staff-login:'.hash('sha256',mb_strtolower($email));}
 public function authenticate(Request $r){
  $d=$r->validate(['email'=>'required|email|max:255','password'=>'required|string|max:128']);$key=$this->key($r,$d['email']);
  if(RateLimiter::tooManyAttempts($key,5))return back()->withErrors(['email'=>'Unable to sign in. Try again later.']);
  $u=User::where('email',mb_strtolower($d['email']))->first();
  $valid=Hash::check($d['password'],$u?->password??'$2y$12$7dC/1oqu1L7LpJAGjRdfOe8j0.QH67rJZiNqJQ7JvO5Hdb0x5oGGW');
  if(!$u||$u->disabled_at||!$valid){RateLimiter::hit($key,600);StaffSecurity::event($u?->id,'password_rejected');return back()->withErrors(['email'=>'Invalid credentials.']);}
  RateLimiter::clear($key);$r->session()->invalidate();$r->session()->regenerateToken();
  if(StaffSecurity::required()){
   if(!$u->totp_secret){StaffSecurity::event($u->id,'enrollment_required');return redirect()->route('login')->withErrors(['email'=>'Staff access requires administrator-enrolled multi-factor authentication.']);}
   $r->session()->put(['pending_staff'=>$u->id,'pending_until'=>time()+300,'pending_version'=>(int)$u->session_version]);return redirect()->route('staff.challenge');
  }
  return $this->finish($r,$u);
 }
 public function challenge(Request $r){if(!$r->session()->has('pending_staff')||time()>$r->session()->get('pending_until',0))return redirect()->route('login');return view('auth.challenge');}
 public function verify(Request $r){
  $d=$r->validate(['code'=>'required|string|max:64']);$id=$r->session()->get('pending_staff');$u=$id?User::find($id):null;
  if(!$u||$u->disabled_at||time()>$r->session()->get('pending_until',0)||$r->session()->get('pending_version')!==(int)$u->session_version){$r->session()->forget(['pending_staff','pending_until','pending_version']);return redirect()->route('login');}
  $key='staff-mfa:'.$u->id;
  if(RateLimiter::tooManyAttempts($key,5)){return redirect()->route('login')->withErrors(['email'=>'Unable to verify. Try again later.']);}
  if(!StaffSecurity::consume($u->id,trim($d['code']))){RateLimiter::hit($key,600);StaffSecurity::event($u->id,'mfa_rejected');return back()->withErrors(['code'=>'Invalid or already used verification code.']);}
  RateLimiter::clear($key);return $this->finish($r,$u);
 }
 private function finish(Request $r,User $u){$r->session()->invalidate();Auth::login($u,false);$r->session()->regenerate();$r->session()->put(['staff_verified'=>$u->id,'staff_last'=>time(),'staff_started'=>time(),'staff_version'=>(int)$u->session_version]);StaffSecurity::event($u->id,'login');return redirect()->route($u->role==='admin'?'admin':'staff');}
 public function logout(Request $r){StaffSecurity::event($r->user()?->id,'logout');Auth::logout();$r->session()->invalidate();$r->session()->regenerateToken();return redirect()->route('login');}
}