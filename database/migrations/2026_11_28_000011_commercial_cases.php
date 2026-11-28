<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration{
 public function up():void{
  Schema::create('commercial_cases',function(Blueprint $t){
   $t->id();$t->foreignId('sourcing_request_id')->nullable()->constrained()->restrictOnDelete();
   $t->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
   $t->string('reference',50)->unique();$t->string('kind',15)->index();$t->string('status',25)->index();
   $t->string('title',180);$t->json('details')->nullable();$t->string('currency',3)->nullable();
   $t->unsignedBigInteger('total_minor')->nullable();$t->date('due_at')->nullable();
   $t->timestamps();$t->index(['kind','status','created_at']);
  });
 }
 public function down():void{Schema::dropIfExists('commercial_cases');}
};