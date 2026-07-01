<?php

namespace Database\Seeders;

use App\Models\BusinessSetting;
use Illuminate\Database\Seeder;

class BrandingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            'primary' => '#5a00a3',
            'secondary' => '#64748b',
            'primary_light' => '#fbf5ff',
            'sidebar_rail_bg' => '#fafafa',
            'primary_dark_text' => '#6ee7b7',
            'sidebar_icon_color' => '#000000',
        ];

        foreach ($settings as $key => $value) {
            BusinessSetting::updateOrCreate(
                ['key' => $key],
                ['value' => $value, 'type' => 'branding']
            );
        }
    }
}
