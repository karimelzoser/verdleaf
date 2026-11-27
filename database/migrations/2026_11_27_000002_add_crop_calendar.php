<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up():void{Schema::create('crop_periods',function(Blueprint $t){$t->id();$t->foreignId('product_id')->constrained()->cascadeOnDelete();$t->string('region');$t->unsignedSmallInteger('crop_year');$t->unsignedTinyInteger('month');$t->string('stage');$t->string('source_url');$t->string('status')->default('draft');$t->timestamps();$t->unique(['product_id','region','crop_year','month','stage'],'unique_crop_period');});}
 public function down():void{Schema::dropIfExists('crop_periods');}
};