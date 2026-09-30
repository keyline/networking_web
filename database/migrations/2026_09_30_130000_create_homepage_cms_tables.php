<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('homepage_sections', function (Blueprint $table) {
            $table->id();
            $table->string('type', 40)->index();
            $table->string('name', 120);
            $table->string('eyebrow', 120)->nullable();
            $table->string('title')->nullable();
            $table->text('body')->nullable();
            $table->json('settings')->nullable();
            $table->unsignedInteger('sort_order')->default(0)->index();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });

        Schema::create('homepage_section_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('homepage_section_id')->constrained('homepage_sections')->cascadeOnDelete();
            $table->string('title')->nullable();
            $table->string('subtitle')->nullable();
            $table->text('body')->nullable();
            $table->string('image')->nullable();
            $table->string('link_label', 80)->nullable();
            $table->string('link_url')->nullable();
            $table->json('meta')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->index(['homepage_section_id', 'sort_order'], 'home_section_items_order_idx');
        });

        $now = now();
        $sectionId = DB::table('homepage_sections')->insertGetId([
            'type' => 'steps',
            'name' => 'How Net-Works helps',
            'eyebrow' => 'How it works',
            'title' => 'Build relationships that grow your business',
            'body' => 'A simple path from joining the community to creating valuable business opportunities.',
            'sort_order' => 20,
            'is_active' => true,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        foreach ([
            ['Create your profile', 'Tell the community about you, your business and the customers you help.'],
            ['Discover members', 'Find trusted professionals by person, business, service or category.'],
            ['Connect and grow', 'Share enquiries, exchange references and meet through community events.'],
        ] as $index => [$title, $body]) {
            DB::table('homepage_section_items')->insert([
                'homepage_section_id' => $sectionId,
                'title' => $title,
                'body' => $body,
                'sort_order' => $index,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        DB::table('homepage_sections')->insert([
            'type' => 'events',
            'name' => 'Upcoming events',
            'eyebrow' => 'Community calendar',
            'title' => 'Meet, learn and build meaningful connections',
            'body' => 'Reserve your place and participate in upcoming Net-Works events.',
            'settings' => json_encode(['limit' => 3]),
            'sort_order' => 60,
            'is_active' => true,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        DB::table('homepage_sections')->insert([
            'type' => 'cta',
            'name' => 'Join call to action',
            'title' => 'Ready to grow your network?',
            'body' => 'Join the community, connect with trusted members and create new opportunities.',
            'settings' => json_encode(['button_label' => 'Join Net-Works', 'button_url' => '/join', 'secondary_label' => 'Member login', 'secondary_url' => '/member']),
            'sort_order' => 100,
            'is_active' => true,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('homepage_section_items');
        Schema::dropIfExists('homepage_sections');
    }
};
