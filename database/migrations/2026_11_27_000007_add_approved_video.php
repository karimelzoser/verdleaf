<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration{
 public function up():void{Schema::table('content_entries',fn(Blueprint $t)=>$t->foreignId('video_asset_id')->nullable()->constrained('media_assets')->nullOnDelete());}
 public function down():void{Schema::table('content_entries',fn(Blueprint $t)=>$t->dropConstrainedForeignId('video_asset_id'));}
};