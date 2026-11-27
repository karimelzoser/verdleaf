<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration{
 public function up():void{Schema::create('activity_log',function(Blueprint $t){$t->id();$t->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();$t->string('event');$t->string('entity');$t->unsignedBigInteger('entity_id');$t->timestamp('created_at');});}
 public function down():void{Schema::dropIfExists('activity_log');}
};