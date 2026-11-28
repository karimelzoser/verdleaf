<?php
namespace App\Http\Controllers;
use App\Models\{Product,Variant,ContentEntry,SourcingRequest,GeneralEnquiry,MediaAsset,PublicDocument,User,Notification,CropPeriod};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
class Dashboard {
 public function index(){
  $now=now();$start=$now->copy()->subDays(29)->startOfDay();
  $counts=['Product families'=>Product::count(),'Published products'=>Product::where('status','published')->count(),'Commercial forms'=>Variant::count(),'New sourcing requests'=>SourcingRequest::where('status','new')->count(),'Enquiries (30 days)'=>GeneralEnquiry::where('created_at','>=',$start)->count(),'Approved media'=>MediaAsset::where('status','published')->count(),'Draft quotations'=>\App\Models\CommercialCase::where('kind','quote')->where('status','draft')->count(),'Open samples'=>\App\Models\CommercialCase::where('kind','sample')->whereNotIn('status',['closed','cancelled'])->count(),'Follow-up tasks'=>\App\Models\CommercialCase::where('kind','task')->whereNotIn('status',['completed','cancelled'])->count()];
  $pipeline=[];foreach(['new','qualified','quoted','negotiating','won','lost'] as $s)$pipeline[$s]=SourcingRequest::where('status',$s)->count();
  $alerts=['Products awaiting review'=>Product::where('status','draft')->count(),'Missing product photography'=>Product::whereNull('media_asset_id')->count(),'Missing Arabic content'=>Product::where(fn($q)=>$q->whereNull('name_ar')->orWhereNull('description_ar'))->count(),'Certificates expiring within 60 days'=>ContentEntry::where('type','certificate')->where('status','published')->whereBetween('expires_at',[today(),today()->addDays(60)])->count(),'Expired certificates'=>ContentEntry::where('type','certificate')->where('status','published')->where('expires_at','<',today())->count(),'Failed notifications'=>Notification::whereNotNull('failed_at')->count(),'Staff missing MFA'=>User::whereNull('totp_secret')->whereNull('disabled_at')->count()];
  $series=[];for($i=29;$i>=0;$i--){$day=$now->copy()->subDays($i)->toDateString();$series[$day]=0;}foreach(DB::table('daily_metrics')->where('event','sourcing_request')->where('day','>=',$start->toDateString())->get() as $r)if(array_key_exists($r->day,$series))$series[$r->day]=$r->count;
  $recent=SourcingRequest::latest()->limit(8)->get(['reference','company','status','created_at']);
  return view('admin.dashboard',compact('counts','pipeline','alerts','series','recent'));
 }
 public function security(){return view('admin.security',['users'=>User::orderBy('name')->get(['id','name','email','role','disabled_at','totp_secret']),'events'=>DB::table('security_events')->latest('id')->paginate(30)]);}
}