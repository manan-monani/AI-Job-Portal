<?php

namespace Database\Seeders;

use App\Models\Tag;
use Illuminate\Database\Seeder;

class TagSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tags = [
            ['name' => 'UI/UX', 'description' => 'User Interface and User Experience Design'],
            ['name' => 'Digital Marketing Tools', 'description' => 'HubSpot, Google Analytics, Mailchimp, etc.'],
            ['name' => 'Communication', 'description' => 'Strong written and verbal communication skills'],
            ['name' => 'Social Media Management', 'description' => 'Platform strategy, content creation, and community engagement'],
            ['name' => 'Data Analysis', 'description' => 'Extracting insights from complex datasets and metrics'],
            ['name' => 'Analytics & Tracking', 'description' => 'Setting up and monitoring KPIs, GA4, and custom tracking'],
            ['name' => 'Paid Advertising', 'description' => 'PPC, Facebook Ads, Google Ads Campaign Management'],
            ['name' => 'Branding', 'description' => 'Brand strategy, identity development, and positioning'],
            ['name' => 'Sales', 'description' => 'B2B/B2C sales, enterprise selling, lead conversion'],
            ['name' => 'Marketing', 'description' => 'General marketing principles and cross-channel strategies'],
            ['name' => 'JavaScript', 'description' => 'Core web programming language and ecosystem'],
            ['name' => 'Laravel', 'description' => 'PHP web application framework'],
            ['name' => 'PHP', 'description' => 'Backend server-side scripting language'],
            ['name' => 'React', 'description' => 'Frontend JavaScript library for building user interfaces'],
            ['name' => 'Next.js', 'description' => 'React framework for production grade applications'],
        ];

        foreach ($tags as $tag) {
            Tag::updateOrCreate(['name' => $tag['name']], [
                'description' => $tag['description'],
                'status' => true,
            ]);
        }

        $this->command->info('Tags seeded successfully.');
    }
}
