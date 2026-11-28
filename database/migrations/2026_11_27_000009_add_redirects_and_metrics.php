<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration{
 public function up():void{
  Schema::create('url_redirects',function(Blueprint $t){$t->id();$t->string('old_path')->unique();$t->string('new_path');$t->timestamps();});
  Schema::create('daily_metrics',function(Blueprint $t){$t->id();$t->date('day');$t->string('event',60);$t->unsignedBigInteger('count')->default(0);$t->unique(['day','event']);});
 }
 public function down():void{Schema::dropIfExists('daily_metrics');Schema::dropIfExists('url_redirects');}
};