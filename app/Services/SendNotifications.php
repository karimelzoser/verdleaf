<?php
namespace App\Services;
use App\Models\{Notification,SourcingRequest,GeneralEnquiry};
use Illuminate\Support\Facades\{Cache,Mail};
class SendNotifications {
 public function run():void{
  if(config('verdleaf.preview')||!config('verdleaf.sales_email'))return;
  Cache::lock('verdleaf-notification-outbox',300)->get(function(){
   $pending=Notification::whereNull('sent_at')->whereNull('failed_at')->where(fn($q)=>$q->whereNull('next_attempt_at')->orWhere('next_attempt_at','<=',now()))->orderBy('id')->limit(20)->get();
   foreach($pending as $item){
    try{
     $record=$item->kind==='sourcing'?SourcingRequest::where('reference',$item->reference)->firstOrFail():GeneralEnquiry::where('reference',$item->reference)->firstOrFail();
     Mail::raw('New Verdleaf enquiry '.$record->reference.'. Sign in to the staff dashboard to review. Do not forward private buyer details.',fn($m)=>$m->to(config('verdleaf.sales_email'))->subject('Verdleaf enquiry '.$record->reference));
     $item->update(['sent_at'=>now()]);
    }catch(\Throwable $e){$attempts=$item->attempts+1;$item->update(['attempts'=>$attempts,'next_attempt_at'=>now()->addMinutes(min(60,2**$attempts)),'failed_at'=>$attempts>=5?now():null]);}
   }
  });
 }
}