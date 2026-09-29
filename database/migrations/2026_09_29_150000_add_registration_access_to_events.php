<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {public function up():void{Schema::table('events',function(Blueprint $t){$t->boolean('requires_login')->default(false)->after('status');$t->json('allowed_user_types')->nullable()->after('requires_login');});}public function down():void{Schema::table('events',fn(Blueprint $t)=>$t->dropColumn(['requires_login','allowed_user_types']));}};
