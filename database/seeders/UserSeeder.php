<?php

namespace Database\Seeders;

use App\Enums\UserType;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = [
            [
                'name' => 'Super Admin',
                'email' => 'admin@admin.com',
                'password' => Hash::make('12345678'),
                'type' => UserType::SUPER_ADMIN,
                'role_slug' => 'super-admin',
            ],
            [
                'name' => 'Manager User',
                'email' => 'manager@admin.com',
                'password' => Hash::make('12345678'),
                'type' => UserType::ADMIN,
                'role_slug' => 'manager',
            ],
            [
                'name' => 'Candidate One',
                'email' => 'candidate1@gmail.com',
                'password' => Hash::make('12345678'),
                'type' => UserType::CUSTOMER,
                'role_slug' => 'customer',
            ],
            [
                'name' => 'Candidate Two',
                'email' => 'candidate2@gmail.com',
                'password' => Hash::make('12345678'),
                'type' => UserType::CUSTOMER,
                'role_slug' => 'customer',
            ],
        ];

        foreach ($users as $userData) {
            $roleSlug = $userData['role_slug'];
            unset($userData['role_slug']);

            $user = User::updateOrCreate(
                ['email' => $userData['email']],
                $userData
            );

            // Ensure profile exists for admins
            if (in_array($user->type, [UserType::SUPER_ADMIN, UserType::ADMIN])) {
                $user->adminProfile()->firstOrCreate([]);
            } else {
                $user->customerProfile()->firstOrCreate([]);
            }

            // Assign role
            $role = Role::where('slug', $roleSlug)->first();
            if ($role) {
                if (! $user->roles()->where('role_id', $role->id)->exists()) {
                    $user->roles()->attach($role);
                }
            } else {
                $this->command->warn("Role with slug '{$roleSlug}' not found for user {$user->email}");
            }
        }

        $this->command->info('Users seeded successfully.');
    }
}
