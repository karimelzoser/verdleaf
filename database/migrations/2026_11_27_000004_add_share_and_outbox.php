<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up():void {
  Schema::create('shortlist_shares',function(Blueprint $t){$t->id();$t->string('token_hash',64)->unique();$t->json('items');$t->timestamp('expires_at');$t->timestamp('revoked_at')->nullable();$t->timestamps();});
  Schema::create('notification_outbox',function(Blueprint $t){$t->id();$t->string('kind');$t->string('reference')->unique();$t->unsignedSmallInteger('attempts')->default(0);$t->timestamp('next_attempt_at')->nullable();$t->timestamp('sent_at')->nullable();$t->timestamp('failed_at')->nullable();$t->timestamps();});
 }
 public function down():void{Schema::dropIfExists('notification_outbox');Schema::dropIfExists('shortlist_shares');}
};