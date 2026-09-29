<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class IndustryMasterSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $industries = [
            'Chemical Industry',
            'Agriculture & allied activities',
            'Agro Products',
            'Automobile Dealers',
            'Banking',
            'Bicycle Manufacturing',
            'Cargo & Courier',
            'Caterer',
            'Catering',
            'Computer & Electronics',
            'Construction',
            'Construction and Hotel Industry',
            'Consultancy Services',
            'Corporate Event & Exhibition',
            'Customized Exhibition Stall Design & Fabrication',
            'Commercial Photography ( Model Shoot / TVC)',
            'Corporate Gifting & Construction',
            'Cosmetics',
            'Courier & Cargo',
            'Education',
            'Electrical',
            'Finance',
            'Finance & Legal Services',
            'Food & Beverage',
            'Furniture',
            'Garments',
            'Handicrafts',
            'Healthcare',
            'Heritage Sweet Makers',
            'Hospitality',
            'Hospitality / HoReCa Supplies',
            'Hospitals',
            'Hotel',
            'Large Format Printing (Flex & Vinyl)',
            'Store Sign Age',
            'In shop Branding',
            'POP Display Items',
            'Neon Sign',
            'LED Sign',
            'ACP Fabrication & Fabric Glow Board etc.',
            'LED Industry',
            'Legal Services',
            'Marketing',
            'Material Handling Equipment Industry',
            'Media',
            'Media & Entertainment',
            'Medical Equipments',
            'News & Media',
            'Packaging',
            'Paper',
            'Pharmaceutical',
            'Plastic Industry',
            'Professional',
            'Public Relations',
            'Restaurant Chain',
            'Retail',
            'Security Services',
            'Shoes & Footwear',
            'Solid Waste Management Solutions',
            'Spices',
            'Sports',
            'Stationery',
            'Sweets',
            'Tours & Travels',
            'Travel Gear',
            'Umbrella',
            'Rainwear',
            'Handbags etc Manufacturer',
            'UPVC Manufacturing'
        ];

        $data = [];

        foreach ($industries as $industry) {
            // Replace '/' with '_'
            $im_tag = strtolower(str_replace([' ', '/', ' & '], '_', $industry));
            $data[] = [
                'im_name' => $industry,
                'im_tag' => $im_tag,
                'im_created_at' => now(),
                'im_updated_at' => now(),
            ];
        }

        DB::table('industry_master')->insert($data);

    }
}
