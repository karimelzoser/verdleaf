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
 public function update(Request $r){$basket=$this->basket($r);$data=$r->validate(['items'=>'nullable|array|max:30','items.*.quantity'=>'required|numeric|min:0.001|max:100000000','items.*.purposes'=>'required|array|min:1|max:3','items.*.purposes.*'=>'in:quote,sample,documents','remove'=>'nullable|integer']);if(isset($data['remove']))unset($basket[$data['remove']]);else foreach($data['items']??[] as $id=>$item)if(isset($basket[$id]))$basket[$id]=$item;$r->session()->put('basket',$basket);return redirect()->route('desk');}
 public function compare(Request $r){$basket=$this->basket($r);$variants=$this->available(array_keys($basket));return view('compare',compact('variants'));}
 public function submit(Request $r){
  abort_if(config('verdleaf.preview'),403,'Preview mode does not accept real enquiries.');
  abort_unless(\App\Models\ContentEntry::visible()->where('type','privacy')->exists(),403,'Privacy information must be approved before accepting enquiries.');
  $data=$r->validate(['company'=>'required|string|max:200','contact_name'=>'required|string|max:120','email'=>'required|email|max:255','country'=>'required|string|max:100','notes'=>'nullable|string|max:3000','consent'=>'accepted','request_token'=>'required|uuid']);
  abort_unless(hash_equals((string)$r->session()->get('request_token'),$data['request_token']),419);
  $basket=$this->basket($r);$variants=$this->available(array_keys($basket));if(!$basket||$variants->count()!==count($basket))throw ValidationException::withMessages(['basket'=>'Review your basket; one or more products are no longer available.']);
  $record=DB::transaction(function()use($data,$basket,$variants){
   $existing=SourcingRequest::where('idempotency_key',$data['request_token'])->first();if($existing)return $existing;
   $record=SourcingRequest::create(['reference'=>'VL-'.Str::upper(Str::random(16)),'idempotency_key'=>$data['request_token'],'company'=>$data['company'],'email'=>$data['email'],'contact_name'=>$data['contact_name'],'country'=>$data['country'],'notes'=>$data['notes']??null,'consent_at'=>now()]);
   foreach($basket as $id=>$item){$v=$variants[$id];$record->items()->create(['variant_id'=>$v->id,'product_name'=>$v->product->name,'form'=>$v->form,'grade'=>$v->grade,'quantity_kg'=>$item['quantity'],'purposes'=>$item['purposes']]);}return $record;
  });
  $r->session()->forget(['basket','request_token']);$r->session()->put('receipt',$record->reference);return redirect()->route('desk.receipt');
 }
 public function receipt(Request $r){abort_unless($reference=$r->session()->get('receipt'),404);return view('receipt',compact('reference'));}
}
