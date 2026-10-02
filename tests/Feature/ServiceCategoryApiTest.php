<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\ServiceCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ServiceCategoryApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_index_returns_banner_style_data_structure(): void
    {
        ServiceCategory::create([
            'name' => 'Hair Styling',
            'status' => true,
        ]);

        $response = $this->getJson('/api/v1/service-categories');

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Service Category List')
            ->assertJsonPath('data.items.0.name', 'Hair Styling');
    }

    public function test_per_page_pagination_for_service_categories(): void
    {
        for ($i = 1; $i <= 5; $i++) {
            ServiceCategory::create([
                'name' => "Category {$i}",
                'status' => true,
            ]);
        }

        $response = $this->getJson('/api/v1/service-categories?per_page=2');

        $response->assertStatus(200)
            ->assertJsonPath('data.page', 1)
            ->assertJsonPath('data.total_page', 3)
            ->assertJsonPath('data.total_items', 5)
            ->assertJsonCount(2, 'data.items');
    }

    public function test_public_show_works_without_auth(): void
    {
        $category = ServiceCategory::create([
            'name' => 'Nail Care',
            'status' => true,
        ]);

        $showResponse = $this->getJson('/api/v1/service-categories/' . $category->id);
        $showResponse->assertStatus(200)
            ->assertJsonPath('data.name', 'Nail Care');
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
}
