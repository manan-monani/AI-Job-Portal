<?php

namespace Tests\Feature;

use App\Models\BusinessSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LegalCenterTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_update_legal_settings()
    {
        $admin = User::factory()->create();
        // Assuming there is a way to give permissions, or we bypass for now if not easily mockable
        // For now, let's assuming actingAs($admin) works if we mock the Gate or give permission.
        // Since we used Gate::authorize, we need to ensure the user has permission.
        // Let's create a role or permission if needed, or simply mock the Gate.

        // Mocking the permission
        \Illuminate\Support\Facades\Gate::define(\App\Constants\Permissions::LEGAL_MANAGEMENT, function () {
            return true;
        });

        $response = $this->actingAs($admin)
            ->post(route('admin.legal.update'), [
                'privacy_policy' => 'New Privacy Policy Content',
                'terms_and_conditions' => 'New Terms Content',
                'about_us' => 'New About Us Content',
            ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect();

        $this->assertDatabaseHas('business_settings', [
            'key' => 'privacy_policy',
            'value' => 'New Privacy Policy Content',
        ]);
    }

    public function test_public_can_view_legal_pages()
    {
        BusinessSetting::create(['key' => 'privacy_policy', 'value' => '# Privacy Policy Header']);

        $response = $this->get('/privacy-policy');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Guest/Legal/Show')
            ->where('title', 'Privacy Policy')
            ->where('content', '# Privacy Policy Header')
        );
    }
}
