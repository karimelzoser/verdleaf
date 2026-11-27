<?php
namespace App\Http\Controllers;
use App\Models\{Product,Variant,ContentEntry,SourcingRequest,GeneralEnquiry};
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
class Staff{
 private function authorize(Request $r,array $roles):void{abort_unless(in_array($r->user()?->role,$roles,true),403);}
 public function index(Request $r){
  $role=$r->user()->role;if($role==='admin')return redirect()->route('admin');
  $this->authorize($r,['editor','quality','sales','analytics']);$data=['role'=>$role];
  if($role==='editor')$data['entries']=ContentEntry::where('status','draft')->latest()->limit(100)->get();
  if($role==='quality'){$data['products']=Product::where('status','draft')->orderBy('name')->get();$data['certificates']=ContentEntry::where('type','certificate')->where('status','draft')->latest()->get();}
  if($role==='sales'){$data['requests']=SourcingRequest::with('items')->latest()->limit(100)->get();$data['general']=GeneralEnquiry::latest()->limit(100)->get();}
  if($role==='analytics')$data['counts']=['Product families'=>Product::count(),'Published products'=>Product::where('status','published')->count(),'Sourcing requests'=>SourcingRequest::count(),'Qualified requests'=>SourcingRequest::where('status','qualified')->count(),'Won requests'=>SourcingRequest::where('status','won')->count(),'General enquiries'=>GeneralEnquiry::count()];
  return view('staff', $data);
 }
 public function draft(Request $r){$this->authorize($r,['editor']);$d=$r->validate(['type'=>'required|in:article,event,visit,story,tour,farm,facility,industry,market,sourcing,export,guide','title'=>'required|string|max:180','body'=>'required|string|max:30000','source_url'=>'nullable|url:http,https|max:1000']);ContentEntry::create($d+['slug'=>Str::slug($d['title']).'-'.Str::lower(Str::random(8)),'status'=>'draft']);return back();}
 public function approveProduct(Request $r,Product $product){$this->authorize($r,['quality']);$r->validate(['approved'=>'accepted']);abort_unless($product->description&&$product->source_url,422,'Evidence and description are required.');$product->update(['status'=>'published','reviewed_at'=>now()]);return back();}
 public function approveCertificate(Request $r,ContentEntry $entry){$this->authorize($r,['quality']);$r->validate(['approved'=>'accepted']);abort_unless($entry->type==='certificate'&&$entry->body&&$entry->expires_at&&!$entry->expires_at->isPast()&&($entry->source_url||$entry->document),422,'Current certificate evidence is required.');$entry->update(['status'=>'published']);return back();}
 public function sourcingStatus(Request $r,SourcingRequest $sourcingRequest){$this->authorize($r,['sales']);$d=$r->validate(['status'=>['required',Rule::in(['new','qualified','quoted','negotiating','won','lost'])]]);$sourcingRequest->update($d);return back();}
 public function generalStatus(Request $r,GeneralEnquiry $enquiry){$this->authorize($r,['sales']);$d=$r->validate(['status'=>'required|in:new,reviewed,closed']);$enquiry->update($d);return back();}
}