<?php
namespace App\Observers;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\{Auth,DB};
class AuditObserver{
 private function record(Model $m,string $event):void{DB::table('activity_log')->insert(['actor_id'=>Auth::id(),'event'=>$event,'entity'=>class_basename($m),'entity_id'=>$m->getKey(),'created_at'=>now()]);}
 public function created(Model $m):void{$this->record($m,'created');}
 public function updated(Model $m):void{$this->record($m,'updated');}
 public function deleted(Model $m):void{$this->record($m,'deleted');}
}