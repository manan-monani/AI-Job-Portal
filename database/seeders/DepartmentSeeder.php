<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Tag;
use Illuminate\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $departments = [
            [
                'name' => 'Engineering',
                'description' => 'Core software development, architecture, and infrastructure teams.',
                'tags' => ['JavaScript', 'Laravel', 'PHP', 'React', 'Next.js', 'Data Analysis'],
            ],
            [
                'name' => 'Marketing',
                'description' => 'Brand awareness, digital campaigns, and market growth strategies.',
                'tags' => ['Marketing', 'Branding', 'Paid Advertising', 'Social Media Management', 'Analytics & Tracking'],
            ],
            [
                'name' => 'Product',
                'description' => 'Product management, user experience, and feature roadmapping.',
                'tags' => ['UI/UX', 'Communication', 'Analytics & Tracking'],
            ],
            [
                'name' => 'Business',
                'description' => 'Sales, enterprise partnerships, and revenue generation.',
                'tags' => ['Sales', 'Communication', 'Digital Marketing Tools'],
            ],
            [
                'name' => 'Human Resources',
                'description' => 'Talent acquisition, employee success, and company culture.',
                'tags' => ['Communication', 'Branding'],
            ],
        ];

        foreach ($departments as $deptData) {
            $tagsToAttach = $deptData['tags'];
            unset($deptData['tags']);
            $deptData['status'] = true;

            $department = Department::updateOrCreate(
                ['name' => $deptData['name']],
                $deptData
            );

            // Fetch tag IDs based on names (Department model doesn't support tags directly)
            // Tags will be mapped to JobPosts in the JobSeeder instead.
        }

        $this->command->info('Departments seeded successfully.');
    }
}
