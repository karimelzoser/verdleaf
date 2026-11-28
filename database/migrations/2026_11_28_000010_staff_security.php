<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up():void{
  Schema::table('users',function(Blueprint $t){$t->text('totp_secret')->nullable();$t->text('recovery_hashes')->nullable();$t->unsignedBigInteger('last_totp_step')->nullable();$t->unsignedBigInteger('session_version')->default(0);$t->timestamp('disabled_at')->nullable();});
  Schema::create('security_events',function(Blueprint $t){$t->id();$t->foreignId('user_id')->nullable()->constrained()->nullOnDelete();$t->string('event',50);$t->timestamp('created_at')->index();});
 }
 public function down():void{Schema::dropIfExists('security_events');Schema::table('users',fn(Blueprint $t)=>$t->dropColumn(['totp_secret','recovery_hashes','last_totp_step','session_version','disabled_at']));}
};