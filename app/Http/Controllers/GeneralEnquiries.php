<?php
namespace App\Http\Controllers;
use App\Models\{ContentEntry,GeneralEnquiry};
use Illuminate\Http\Request;
use Illuminate\Support\Str;
class GeneralEnquiries {
 const TYPES=['contact'=>'Contact our export team','visit'=>'Request a business visit','meeting'=>'Request an exhibition meeting','custom'=>'Request a custom ingredient'];
 public function index(Request $r,string $type){abort_unless(isset(self::TYPES[$type]),404);return view($r->routeIs('ar.*')?'ar.enquiry':'enquiry',['type'=>$type,'title'=>self::TYPES[$type]]);}
 public function store(Request $r,string $type){
  abort_unless(isset(self::TYPES[$type]),404);abort_if(config('verdleaf.preview'),403);
  abort_unless(ContentEntry::visible()->where('type','privacy')->exists(),403,'Privacy information must be approved.');
  $d=$r->validate(['company'=>'required|string|max:200','name'=>'required|string|max:120','email'=>'required|email|max:255','country'=>'required|string|max:100','message'=>'required|string|max:3000','requested_date'=>'nullable|date|after_or_equal:today','consent'=>'accepted','website'=>'nullable|size:0']);
  $record=GeneralEnquiry::create(array_intersect_key($d,array_flip(['company','name','email','country','message','requested_date']))+['reference'=>'VL-'.Str::upper(Str::random(16)),'type'=>$type,'consent_at'=>now()]);
  \App\Models\Notification::firstOrCreate(['reference'=>$record->reference],['kind'=>'general','next_attempt_at'=>now()]);
  $r->session()->put('receipt',$record->reference);return redirect()->route($r->routeIs('ar.*')?'ar.desk.receipt':'desk.receipt');
 }
 public function status(Request $r,GeneralEnquiry $enquiry){abort_unless($r->user()?->role==='admin',403);$d=$r->validate(['status'=>'required|in:new,reviewed,closed']);$enquiry->update($d);return back();}
}