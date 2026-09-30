<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\ServiceCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ServiceCategoryApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_index_and_show_work_without_auth(): void
    {
        $category = ServiceCategory::create([
            'name' => 'Hair Styling',
            'status' => true,
        ]);

        $response = $this->getJson('/api/v1/service-categories');
        $response->assertStatus(200)
            ->assertJsonPath('data.0.name', 'Hair Styling');

        $showResponse = $this->getJson('/api/v1/service-categories/' . $category->id);
        $showResponse->assertStatus(200)
            ->assertJsonPath('data.name', 'Hair Styling');
    }

    public function test_store_update_destroy_require_auth(): void
    {
        $this->postJson('/api/v1/service-categories', ['name' => 'Spa'])
            ->assertStatus(401);

        $category = ServiceCategory::create(['name' => 'Test']);

        $this->putJson('/api/v1/service-categories/' . $category->id, ['name' => 'Updated'])
            ->assertStatus(401);

        $this->deleteJson('/api/v1/service-categories/' . $category->id)
            ->assertStatus(401);
    }

    public function test_authenticated_user_can_crud_category(): void
    {
        $user = User::factory()->create();

        // Create
        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/service-categories', [
                'name' => 'Skin Care',
                'icon' => 'fa-face-smile',
                'orderby' => 1,
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.name', 'Skin Care')
            ->assertJsonPath('data.slug', 'skin-care');

        $categoryId = $response->json('data.id');

        // Update
        $this->actingAs($user, 'sanctum')
            ->putJson('/api/v1/service-categories/' . $categoryId, [
                'name' => 'Advanced Skin Care',
            ])
            ->assertStatus(200)
            ->assertJsonPath('data.name', 'Advanced Skin Care')
            ->assertJsonPath('data.slug', 'advanced-skin-care');

        // Destroy
        $this->actingAs($user, 'sanctum')
            ->deleteJson('/api/v1/service-categories/' . $categoryId)
            ->assertStatus(200);

        $this->assertDatabaseMissing('service_categories', ['id' => $categoryId]);
    }

    public function test_category_slug_auto_generation_and_preservation_on_update(): void
    {
        $user = User::factory()->create();

        $category = ServiceCategory::create(['name' => 'Nail Art']);
        $this->assertEquals('nail-art', $category->slug);

        // Update without changing name -> slug should remain 'nail-art'
        $this->actingAs($user, 'sanctum')
            ->putJson('/api/v1/service-categories/' . $category->id, [
                'icon' => 'fa-star',
            ])
            ->assertStatus(200)
            ->assertJsonPath('data.slug', 'nail-art');
    }
}
