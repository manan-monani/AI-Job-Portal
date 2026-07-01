<?php

namespace Database\Seeders;

use App\Models\BusinessSetting;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class BusinessBrandingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Starting Business Branding Seeding...');

        // 1. Define source and destination
        $sourceDir = database_path('seeders/dummy_image');
        $destSubDir = 'branding';
        $destDir = storage_path('app/public/'.$destSubDir);

        // 2. Ensure destination exists
        if (! File::exists($destDir)) {
            File::makeDirectory($destDir, 0755, true);
        }

        $assets = [
            'logo_url' => 'logo.png',
            'favicon_url' => 'favicon.png',
            'cover_url' => 'hero.png',
        ];

        $settings = [
            'business_name' => '6amTech Career',
            'primary' => '#5a00a3',
            'secondary' => '#64748b',
            'primary_light' => '#fbf5ff',
            'sidebar_rail_bg' => '#fafafa',
            'primary_dark_text' => '#6ee7b7',
            'sidebar_icon_color' => '#000000',
            'email_sending_enabled' => 0,
        ];

        // 3. Process Assets
        foreach ($assets as $key => $filename) {
            $sourceFile = $sourceDir.'/'.$filename;

            if (! File::exists($sourceFile)) {
                throw new \Exception("Source image file missing: {$sourceFile}");
            }

            $destFile = $destDir.'/'.$filename;
            File::copy($sourceFile, $destFile);

            $settings[$key] = $destSubDir.'/'.$filename;
            $this->command->line("Copied {$filename} to storage/branding/");
        }

        // 4. Update Database
        foreach ($settings as $key => $value) {
            BusinessSetting::updateOrCreate(
                ['key' => $key],
                ['value' => $value, 'type' => 'branding']
            );
        }

        $this->command->info('Business Branding Seeder completed successfully.');
    }
}
