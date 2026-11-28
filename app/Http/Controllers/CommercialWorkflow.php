<?php
namespace App\Http\Controllers;
use App\Models\{CommercialCase,SourcingRequest};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
class CommercialWorkflow{
 const FLOW=['quote'=>['draft'=>['approved','cancelled'],'approved'=>['sent','cancelled'],'sent'=>['accepted','declined','cancelled'],'accepted'=>[],'declined'=>[],'cancelled'=>[]],
 'sample'=>['requested'=>['approved','cancelled'],'approved'=>['prepared','cancelled'],'prepared'=>['dispatched','cancelled'],'dispatched'=>['closed'],'closed'=>[],'cancelled'=>[]],
 'task'=>['open'=>['in_progress','completed','cancelled'],'in_progress'=>['completed','cancelled'],'completed'=>[],'cancelled'=>[]]];
 public function index(Request $r){
  $kind=$r->query('kind','quote');abort_unless(is_string($kind)&&isset(self::FLOW[$kind]),404);
  $cases=CommercialCase::where('kind',$kind)->with('sourcingRequest:id,reference,company')->latest()->paginate(20)->withQueryString();
  $requests=SourcingRequest::latest()->limit(100)->get(['id','reference','company']);
  return view('admin.commercial',compact('kind','cases','requests'));
 }
 public function store(Request $r){
  $d=$r->validate(['kind'=>'required|in:quote,sample,task','sourcing_request_id'=>'nullable|required_unless:kind,task|exists:sourcing_requests,id','title'=>'required|string|max:180','due_at'=>'nullable|date|after_or_equal:today','notes'=>'nullable|string|max:3000']);
  $case=DB::transaction(function()use($r,$d){
   $source=isset($d['sourcing_request_id'])?SourcingRequest::with('items')->lockForUpdate()->findOrFail($d['sourcing_request_id']):null;
   $lines=$source?$source->items->map(fn($i)=>['item_id'=>$i->id,'product'=>$i->product_name,'form'=>$i->form,'grade'=>$i->grade,'quantity_kg'=>(string)$i->quantity_kg])->values()->all():[];
   if($d['kind']==='quote'&&!count($lines))throw ValidationException::withMessages(['sourcing_request_id'=>'The request has no ingredient lines.']);
   return CommercialCase::create(['kind'=>$d['kind'],'status'=>array_key_first(self::FLOW[$d['kind']]),'sourcing_request_id'=>$source?->id,'title'=>$d['title'],'reference'=>strtoupper(substr($d['kind'],0,2)).'-'.Str::upper(Str::random(16)),'created_by'=>$r->user()->id,'due_at'=>$d['due_at']??null,'details'=>['notes'=>$d['notes']??'','lines'=>$lines]]);
  });
  return redirect()->route('admin.commercial.edit',$case)->with('message','Commercial record created.');
 }
 public function edit(CommercialCase $case){return view('admin.commercial-edit',['case'=>$case->load('sourcingRequest'),'next'=>self::FLOW[$case->kind][$case->status]??[]]);}
 public function price(Request $r,CommercialCase $case){
  $d=$r->validate(['currency'=>'required|in:USD,EUR,EGP','terms'=>'required|in:EXW,FCA,FOB,CIF,DAP','valid_until'=>'required|date|after_or_equal:today','prices'=>'required|array|max:100','prices.*'=>'required|integer|min:1|max:1000000']);
  DB::transaction(function()use($case,$d){
   $c=CommercialCase::lockForUpdate()->findOrFail($case->id);abort_unless($c->kind==='quote'&&$c->status==='draft',409);
   $details=$c->details;$lines=$details['lines']??[];$total=0;
   if(count($lines)!==count($d['prices']))throw ValidationException::withMessages(['prices'=>'Price every quotation line.']);
   foreach($lines as $i=>&$line){
    if(!array_key_exists($i,$d['prices']))throw ValidationException::withMessages(['prices'=>'Missing quotation line price.']);
    $grams=(int)round((float)$line['quantity_kg']*1000);
    if($grams<=0||$grams>1000000000)throw ValidationException::withMessages(['prices'=>'Quantity exceeds supported quotation range.']);
    $line['unit_minor']=(int)$d['prices'][$i];$line['line_minor']=intdiv($grams*$line['unit_minor']+500,1000);$total+=$line['line_minor'];
   }unset($line);
   $details['lines']=$lines;$details['terms']=$d['terms'];$details['valid_until']=$d['valid_until'];
   $c->update(['currency'=>$d['currency'],'total_minor'=>$total,'details'=>$details]);
  });return back()->with('message','Draft prices saved; approval required before sending.');
 }
 public function transition(Request $r,CommercialCase $case){
  $d=$r->validate(['status'=>'required|string|max:25']);
  DB::transaction(function()use($case,$d,$r){
   $c=CommercialCase::lockForUpdate()->findOrFail($case->id);
   abort_unless(in_array($d['status'],self::FLOW[$c->kind][$c->status]??[],true),409,'Invalid status transition.');
   if($c->kind==='quote'&&in_array($d['status'],['approved','sent'],true)){
    $r->validate(['confirmed'=>'accepted']);
    abort_unless($c->total_minor>0&&$c->currency&&isset($c->details['valid_until'])&&$c->details['valid_until']>=today()->toDateString(),422,'Price and valid terms are required.');
   }
   $c->update(['status'=>$d['status']]);
  });return back()->with('message','Status recorded. No email or shipment was sent automatically.');
 }
 public function print(CommercialCase $case){abort_unless($case->kind==='quote'&&$case->total_minor!==null,404);return view('admin.quote-print',['case'=>$case->load('sourcingRequest')]);}
}