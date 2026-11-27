<?php
namespace App\Http\Controllers;
use App\Models\{ShortlistShare,Variant};
use Illuminate\Http\Request;
use Illuminate\Support\Str;
class Shortlists {
 public function create(Request $r){
  $items=$r->session()->get('basket',[]);abort_unless($items&&count($items)<=30,422);
  $token=Str::random(64);$share=ShortlistShare::create(['token_hash'=>hash('sha256',$token),'items'=>array_keys($items),'expires_at'=>now()->addDays(7)]);
  $owned=$r->session()->get('shares',[]);$owned[]=$share->id;$r->session()->put('shares',array_slice($owned,-30));
  return redirect()->route('desk')->with('share_url',route('shortlist.show',$token))->with('share_id',$share->id);
 }
 public function show(string $token){
  abort_unless(strlen($token)===64,404);
  $share=ShortlistShare::where('token_hash',hash('sha256',$token))->whereNull('revoked_at')->where('expires_at','>',now())->firstOrFail();
  $variants=Variant::visible()->whereIn('id',$share->items)->whereHas('product',fn($p)=>$p->visible())->with('product')->get();
  return view('shortlist',compact('share','variants'));
 }
 public function revoke(Request $r,ShortlistShare $share){abort_unless(in_array($share->id,$r->session()->get('shares',[]),true),403);$share->update(['revoked_at'=>now()]);return redirect()->route('desk')->with('message','Shared link revoked.');}
}