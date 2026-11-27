<?php
namespace App\Http\Controllers;
use App\Models\Variant;
use App\Models\SourcingRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
class BuyerDesk {
 private function available(array $ids){return Variant::visible()->whereIn('id',$ids)->whereHas('product',fn($p)=>$p->visible())->with('product')->get()->keyBy('id');}
 private function basket(Request $r):array{return $r->session()->get('basket',[]);}
 public function index(Request $r){$basket=$this->basket($r);$variants=$this->available(array_keys($basket));if(!$r->session()->has('request_token'))$r->session()->put('request_token',Str::uuid()->toString());return view('desk',compact('basket','variants'));}
 public function add(Request $r){$d=$r->validate(['variant_id'=>'required|integer']);$id=$d['variant_id'];abort_unless($this->available([$id])->has($id),404);$basket=$this->basket($r);if(!isset($basket[$id])&&count($basket)>=30)throw ValidationException::withMessages(['basket'=>'Limit 30 variants.']);$basket[$id]??=['quantity'=>1000,'purposes'=>['quote']];$r->session()->put('basket',$basket);return redirect()->route('desk');}
 public function update(Request $r){$basket=$this->basket($r);if($r->filled('remove')){$remove=$r->validate(['remove'=>'required|integer']);unset($basket[$remove['remove']]);$r->session()->put('basket',$basket);return redirect()->route('desk');}$data=$r->validate(['items'=>'nullable|array|max:30','items.*.quantity'=>'required|numeric|min:0.001|max:100000000|decimal:0,3','items.*.purposes'=>'required|array|min:1|max:3','items.*.purposes.*'=>'in:quote,sample,documents','remove'=>'nullable|integer']);if(isset($data['remove']))unset($basket[$data['remove']]);else foreach($data['items']??[] as $id=>$item)if(isset($basket[$id]))$basket[$id]=$item;$r->session()->put('basket',$basket);return redirect()->route('desk');}
 public function compare(Request $r){$basket=$this->basket($r);$variants=$this->available(array_keys($basket));return view('compare',compact('variants'));}
 public function submit(Request $r){
  abort_if(config('verdleaf.preview'),403,'Preview mode does not accept real enquiries.');
  abort_unless(\App\Models\ContentEntry::visible()->where('type','privacy')->exists(),403,'Privacy information must be approved before accepting enquiries.');
  $data=$r->validate(['company'=>'required|string|max:200','contact_name'=>'required|string|max:120','email'=>'required|email|max:255','country'=>'required|string|max:100','notes'=>'nullable|string|max:3000','destination_port'=>'nullable|string|max:180','incoterm'=>'nullable|in:EXW,FCA,FOB,CFR,CIF,CPT,CIP,DAP,DPU,DDP,Other','delivery_date'=>'nullable|date|after_or_equal:today','packaging_preference'=>'nullable|string|max:200','attachment'=>'nullable|file|mimes:pdf|max:5120','consent'=>'accepted','request_token'=>'required|uuid']);
  abort_unless(hash_equals((string)$r->session()->get('request_token'),$data['request_token']),419);
  $basket=$this->basket($r);$variants=$this->available(array_keys($basket));if(!$basket||$variants->count()!==count($basket))throw ValidationException::withMessages(['basket'=>'Review your basket; one or more products are no longer available.']);
  $attachment=null;$attachmentName=null;
  if($r->hasFile('attachment')){
   $file=$r->file('attachment');$h=fopen($file->getRealPath(),'rb');$magic=fread($h,5);fclose($h);
   if($magic!=='%PDF-')throw ValidationException::withMessages(['attachment'=>'Upload a valid PDF document.']);
   $attachment=$file->store('private-rfq','local');abort_unless($attachment,500);$attachmentName=mb_substr(basename($file->getClientOriginalName()),0,200);
  }
  try{$record=DB::transaction(function()use($data,$basket,$variants,$attachment,$attachmentName){
   $existing=SourcingRequest::where('idempotency_key',$data['request_token'])->first();if($existing)return $existing;
   $record=SourcingRequest::create(['reference'=>'VL-'.Str::upper(Str::random(16)),'idempotency_key'=>$data['request_token'],'company'=>$data['company'],'email'=>$data['email'],'contact_name'=>$data['contact_name'],'country'=>$data['country'],'notes'=>$data['notes']??null,'destination_port'=>$data['destination_port']??null,'incoterm'=>$data['incoterm']??null,'delivery_date'=>$data['delivery_date']??null,'packaging_preference'=>$data['packaging_preference']??null,'attachment_path'=>$attachment,'attachment_name'=>$attachmentName,'consent_at'=>now()]);
   foreach($basket as $id=>$item){$v=$variants[$id];$record->items()->create(['variant_id'=>$v->id,'product_name'=>$v->product->name,'form'=>$v->form,'grade'=>$v->grade,'quantity_kg'=>$item['quantity'],'purposes'=>$item['purposes']]);} \App\Models\Notification::firstOrCreate(['reference'=>$record->reference],['kind'=>'sourcing','next_attempt_at'=>now()]);return $record;
  });}catch(\Throwable $e){if($attachment)\Illuminate\Support\Facades\Storage::disk('local')->delete($attachment);throw $e;}
  if($attachment&&$record->attachment_path!==$attachment)\Illuminate\Support\Facades\Storage::disk('local')->delete($attachment);
  $r->session()->forget(['basket','request_token']);$r->session()->put('receipt',$record->reference);return redirect()->route('desk.receipt');
 }
 public function receipt(Request $r){abort_unless($reference=$r->session()->get('receipt'),404);return view('receipt',compact('reference'));}
}
