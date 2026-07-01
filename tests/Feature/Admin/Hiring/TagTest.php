<?php

namespace Tests\Feature\Admin\Hiring;

use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->admin = User::factory()->create(['type' => \App\Enums\UserType::SUPER_ADMIN]);
});

test('admin can view tags index', function () {
    Tag::factory()->count(3)->create();

    $response = $this->actingAs($this->admin)
        ->get(route('admin.tags.index'));

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page
        ->component('Admin/Hiring/Tags/Index')
        ->has('tags.data', 3)
    );
});

test('admin can create a tag', function () {
    $response = $this->actingAs($this->admin)
        ->post(route('admin.tags.store'), [
            'name' => 'Laravel',
            'description' => 'PHP framework skill',
            'status' => true,
        ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('tags', ['name' => 'Laravel']);
});

test('admin can update a tag', function () {
    $tag = Tag::factory()->create(['name' => 'Old Skill']);

    $response = $this->actingAs($this->admin)
        ->put(route('admin.tags.update', $tag), [
            'name' => 'New Skill',
            'description' => 'Updated description',
            'status' => true,
        ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('tags', ['id' => $tag->id, 'name' => 'New Skill']);
});

test('admin can update tag status', function () {
    $tag = Tag::factory()->create(['status' => true]);

    $response = $this->actingAs($this->admin)
        ->patch(route('admin.tags.status', $tag), [
            'status' => false,
        ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('tags', ['id' => $tag->id, 'status' => false]);
});

test('admin can delete a tag', function () {
    $tag = Tag::factory()->create();

    $response = $this->actingAs($this->admin)
        ->delete(route('admin.tags.destroy', $tag));

    $response->assertRedirect();
    $this->assertSoftDeleted('tags', ['id' => $tag->id]);
});
