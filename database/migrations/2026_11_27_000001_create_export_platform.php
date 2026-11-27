<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up():void {
  Schema::create('users',function(Blueprint $t){$t->id();$t->string('name');$t->string('email')->unique();$t->string('password');$t->string('role')->default('editor');$t->rememberToken();$t->timestamps();});
  Schema::create('products',function(Blueprint $t){$t->id();$t->string('slug')->unique();$t->string('name');$t->string('name_ar')->nullable();$t->string('botanical_name')->nullable();$t->string('category')->index();$t->text('description')->nullable();$t->text('description_ar')->nullable();$t->string('origin')->nullable();$t->json('applications')->nullable();$t->json('aliases')->nullable();$t->string('status')->default('draft')->index();$t->text('source_url')->nullable();$t->timestamp('reviewed_at')->nullable();$t->timestamps();});
  Schema::create('variants',function(Blueprint $t){$t->id();$t->foreignId('product_id')->constrained()->restrictOnDelete();$t->string('sku')->unique();$t->string('form');$t->string('grade')->nullable();$t->text('description')->nullable();$t->json('specifications')->nullable();$t->json('packaging')->nullable();$t->string('status')->default('draft')->index();$t->timestamps();});
  Schema::create('content_entries',function(Blueprint $t){$t->id();$t->string('type')->index();$t->string('slug');$t->string('title');$t->text('body')->nullable();$t->json('metadata')->nullable();$t->string('status')->default('draft')->index();$t->date('event_date')->nullable();$t->date('expires_at')->nullable();$t->text('source_url')->nullable();$t->timestamps();$t->unique(['type','slug']);});
  Schema::create('sourcing_requests',function(Blueprint $t){$t->id();$t->string('reference')->unique();$t->string('idempotency_key')->unique();$t->string('company');$t->string('email');$t->string('contact_name');$t->string('country');$t->text('notes')->nullable();$t->string('status')->default('new')->index();$t->timestamp('consent_at');$t->timestamps();});
  Schema::create('sourcing_items',function(Blueprint $t){$t->id();$t->foreignId('sourcing_request_id')->constrained()->cascadeOnDelete();$t->foreignId('variant_id')->constrained()->restrictOnDelete();$t->string('product_name');$t->string('form');$t->string('grade')->nullable();$t->decimal('quantity_kg',13,3);$t->json('purposes');$t->timestamps();});
 }
 public function down():void {foreach(['sourcing_items','sourcing_requests','content_entries','variants','products','users'] as $table)Schema::dropIfExists($table);}
};
