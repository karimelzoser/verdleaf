<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up():void {
  Schema::create('media_assets',function(Blueprint $t){$t->id();$t->string('path')->unique();$t->string('mime');$t->string('alt');$t->string('credit');$t->string('status')->default('draft');$t->timestamps();});
  foreach(['products','variants','content_entries'] as $table)Schema::table($table,function(Blueprint $t){$t->foreignId('media_asset_id')->nullable()->constrained('media_assets')->nullOnDelete();});
  Schema::table('content_entries',function(Blueprint $t){$t->string('title_ar')->nullable();$t->text('body_ar')->nullable();});
  Schema::create('general_enquiries',function(Blueprint $t){$t->id();$t->string('reference')->unique();$t->string('type');$t->string('company');$t->string('name');$t->string('email');$t->string('country');$t->text('message');$t->date('requested_date')->nullable();$t->string('status')->default('new');$t->timestamp('consent_at');$t->timestamps();});
 }
 public function down():void {Schema::dropIfExists('general_enquiries');Schema::table('content_entries',function(Blueprint $t){$t->dropColumn(['title_ar','body_ar']);});foreach(['products','variants','content_entries'] as $table)Schema::table($table,function(Blueprint $t){$t->dropConstrainedForeignId('media_asset_id');});Schema::dropIfExists('media_assets');}
};