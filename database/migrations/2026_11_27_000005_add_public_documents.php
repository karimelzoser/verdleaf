<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration{
 public function up():void{
  Schema::create('public_documents',function(Blueprint $t){$t->id();$t->string('path')->unique();$t->string('title');$t->string('kind');$t->string('revision')->nullable();$t->string('status')->default('draft');$t->date('expires_at')->nullable();$t->timestamps();});
  foreach(['products','variants','content_entries'] as $table)Schema::table($table,function(Blueprint $t){$t->foreignId('public_document_id')->nullable()->constrained('public_documents')->nullOnDelete();});
 }
 public function down():void{foreach(['products','variants','content_entries'] as $table)Schema::table($table,fn(Blueprint $t)=>$t->dropConstrainedForeignId('public_document_id'));Schema::dropIfExists('public_documents');}
};