<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up():void {Schema::create('bulk_import_jobs',function(Blueprint $t){$t->id();$t->string('type',30);$t->string('original_name');$t->string('stored_path');$t->unsignedSmallInteger('header_row')->default(1);$t->json('headers');$t->json('mapping')->nullable();$t->json('options')->nullable();$t->string('status',20)->default('uploaded');$t->unsignedInteger('total_rows')->default(0);$t->unsignedInteger('created_rows')->default(0);$t->unsignedInteger('updated_rows')->default(0);$t->unsignedInteger('skipped_rows')->default(0);$t->json('errors')->nullable();$t->unsignedBigInteger('created_by')->nullable();$t->timestamp('completed_at')->nullable();$t->timestamps();$t->index(['status','created_at']);});}
 public function down():void {Schema::dropIfExists('bulk_import_jobs');}
};
