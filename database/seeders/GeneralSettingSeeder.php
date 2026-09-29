<?php

namespace Database\Seeders;

use App\Models\GeneralSetting;
use Illuminate\Database\Seeder;

class GeneralSettingSeeder extends Seeder
{
    /**
     * Seed safe local defaults. Runtime credentials must be configured through
     * environment variables or the administration settings screen.
     */
    public function run(): void
    {
        GeneralSetting::query()->firstOrCreate(
            ['id' => 1],
            [
                'site_name' => 'Net-Works',
                'site_mail' => 'admin@example.com',
                'system_email' => 'admin@example.com',
                'site_url' => config('app.url'),
                'from_email' => 'admin@example.com',
                'from_name' => 'Net-Works',
            ]
        );
    }
}
