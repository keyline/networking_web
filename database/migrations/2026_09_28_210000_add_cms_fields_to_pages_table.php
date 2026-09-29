<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pages', function (Blueprint $table) {
            $table->unsignedBigInteger('parent_id')->nullable()->after('page_slug');
            $table->string('nav_label')->nullable()->after('parent_id');
            $table->string('nav_location', 20)->default('none')->after('nav_label');
            $table->unsignedInteger('nav_order')->default(0)->after('nav_location');
            $table->string('template', 40)->default('default')->after('nav_order');
            $table->string('meta_title')->nullable()->after('template');
            $table->text('meta_description')->nullable()->after('meta_title');
            $table->timestamp('published_at')->nullable()->after('meta_description');

            $table->index(['nav_location', 'nav_order']);
            $table->index('parent_id');
        });
    }

    public function down(): void
    {
        Schema::table('pages', function (Blueprint $table) {
            $table->dropIndex(['nav_location', 'nav_order']);
            $table->dropIndex(['parent_id']);
            $table->dropColumn([
                'parent_id', 'nav_label', 'nav_location', 'nav_order', 'template',
                'meta_title', 'meta_description', 'published_at',
            ]);
        });
    }
};
