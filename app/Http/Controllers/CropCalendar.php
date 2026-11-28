<?php
namespace App\Http\Controllers;
use App\Models\{Product,CropPeriod};
use Illuminate\Http\Request;
class CropCalendar {
 public function index(Request $r){
  $year=(int)$r->query('year',now()->year);
  $region=mb_substr((string)$r->query('region',''),0,150);
  $q=CropPeriod::where('status','published')->where('crop_year',$year)->whereHas('product',fn($p)=>$p->where('status','published'))->with('product');
  if($region!=='')$q->where('region',$region);
  $rows=$q->orderBy('region')->get()->groupBy(fn($p)=>$p->product_id.'|'.$p->region);
  return view('calendar',compact('year','region','rows'));
 }
 public function save(Request $r){
  abort_unless($r->user()?->role==='admin',403);
  $d=$r->validate(['product_id'=>'required|exists:products,id','region'=>'required|string|max:150','crop_year'=>'required|integer|min:2000|max:2100','month'=>'required|integer|min:1|max:12','stage'=>'required|in:planting,growing,harvest','source_url'=>'required|url:http,https|max:255','status'=>'required|in:draft,published']);
  if($d['status']==='published')$r->validate(['approved'=>'accepted']);
  CropPeriod::updateOrCreate(array_intersect_key($d,array_flip(['product_id','region','crop_year','month','stage'])),['source_url'=>$d['source_url'],'status'=>$d['status']]);return back()->with('message','Crop period saved.');
 }
}
