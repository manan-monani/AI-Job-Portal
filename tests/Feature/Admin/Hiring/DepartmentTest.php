<?php

namespace Tests\Feature\Admin\Hiring;

use App\Models\Department;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->admin = User::factory()->create(['type' => \App\Enums\UserType::SUPER_ADMIN]);
});

test('admin can view departments index', function () {
    Department::factory()->count(3)->create();

    $response = $this->actingAs($this->admin)
        ->get(route('admin.departments.index'));

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page
        ->component('Admin/Hiring/Departments/Index')
        ->has('departments.data', 3)
    );
});

test('admin can create a department', function () {
    $response = $this->actingAs($this->admin)
        ->post(route('admin.departments.store'), [
            'name' => 'Engineering',
            'description' => 'Product development team',
            'status' => true,
        ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('departments', ['name' => 'Engineering']);
});

test('admin can update a department', function () {
    $department = Department::factory()->create(['name' => 'Old Name']);

    $response = $this->actingAs($this->admin)
        ->put(route('admin.departments.update', $department), [
            'name' => 'New Name',
            'description' => 'Updated description',
            'status' => true,
        ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('departments', ['id' => $department->id, 'name' => 'New Name']);
});

test('admin can update department status', function () {
    $department = Department::factory()->create(['status' => true]);

    $response = $this->actingAs($this->admin)
        ->patch(route('admin.departments.status', $department), [
            'status' => false,
        ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('departments', ['id' => $department->id, 'status' => false]);
});

test('admin can delete a department', function () {
    $department = Department::factory()->create();

    $response = $this->actingAs($this->admin)
        ->delete(route('admin.departments.destroy', $department));

    $response->assertRedirect();
    $this->assertSoftDeleted('departments', ['id' => $department->id]);
});
