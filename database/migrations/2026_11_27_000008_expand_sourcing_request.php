<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration{
 public function up():void{
  Schema::table('sourcing_requests',function(Blueprint $t){$t->string('destination_port')->nullable();$t->string('incoterm')->nullable();$t->date('delivery_date')->nullable();$t->string('packaging_preference')->nullable();$t->string('attachment_path')->nullable();$t->string('attachment_name')->nullable();});
  Schema::table('notification_outbox',function(Blueprint $t){$t->timestamp('staff_sent_at')->nullable();$t->timestamp('buyer_sent_at')->nullable();});
 }
 public function down():void{Schema::table('notification_outbox',fn(Blueprint $t)=>$t->dropColumn(['staff_sent_at','buyer_sent_at']));Schema::table('sourcing_requests',fn(Blueprint $t)=>$t->dropColumn(['destination_port','incoterm','delivery_date','packaging_preference','attachment_path','attachment_name']));}
};