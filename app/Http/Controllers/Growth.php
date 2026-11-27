<?php
namespace App\Http\Controllers;
use App\Models\UrlRedirect;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
class Growth{
 public function index(Request $r){abort_unless($r->user()?->role==='admin',403);return view('admin.growth',['redirects'=>UrlRedirect::orderBy('old_path')->get(),'metrics'=>DB::table('daily_metrics')->where('day','>=',now()->subDays(30)->toDateString())->orderByDesc('day')->get()]);}
 public function save(Request $r){
  abort_unless($r->user()?->role==='admin',403);
  $d=$r->validate(['old_path'=>'required|string|max:220|regex:~^/[A-Za-z0-9_/-]+$~','new_path'=>'required|string|max:220|regex:~^/[A-Za-z0-9_/-]*$~']);
  $old=rtrim($d['old_path'],'/');$new=rtrim($d['new_path'],'/')?:'/';
  foreach([$old,$new] as $path)abort_if(str_starts_with($path,'//')||preg_match('~^/(admin|staff|login|logout|buyer-desk|ar/buyer-desk|shortlist|media|documents)(/|$)~',$path),422);
  abort_if($old===''||$old===$new||UrlRedirect::where('old_path',$new)->exists()||UrlRedirect::where('new_path',$old)->exists(),422,'Redirect chain or loop is not allowed.');
  UrlRedirect::updateOrCreate(['old_path'=>$old],['new_path'=>$new]);return back();
 }
 public function remove(Request $r,UrlRedirect $redirect){abort_unless($r->user()?->role==='admin',403);$redirect->delete();return back();}
 public function missing(Request $r){$path='/'.trim($r->path(),'/');$target=UrlRedirect::where('old_path',$path)->value('new_path');if($target&&$r->isMethod('GET'))return redirect($target,301);abort(404);}
}