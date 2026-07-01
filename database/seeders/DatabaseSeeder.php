<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            UserSeeder::class,
            BusinessBrandingSeeder::class,
            TagSeeder::class,
            DepartmentSeeder::class,
            JobSeeder::class,
            PipelineSeeder::class,
            ApplicationSeeder::class,
            EvaluationSeeder::class,
        ]);
    }
}
