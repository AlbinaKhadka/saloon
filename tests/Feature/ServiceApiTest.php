<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\ServiceCategory;
use App\Models\Service;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ServiceApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_index_returns_banner_style_pagination_structure(): void
    {
        $category = ServiceCategory::create(['name' => 'Hair', 'slug' => 'hair']);
        Service::create([
            'service_category_id' => $category->id,
            'title' => 'Hair Cut',
            'price' => 50.00,
            'image' => 'services/test.jpg',
            'status' => 1,
        ]);

        $response = $this->getJson('/api/v1/services');

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Service List')
            ->assertJsonPath('data.items.0.title', 'Hair Cut');
    }

    public function test_services_per_page_pagination(): void
    {
        $category = ServiceCategory::create(['name' => 'Hair', 'slug' => 'hair']);
        for ($i = 1; $i <= 5; $i++) {
            Service::create([
                'service_category_id' => $category->id,
                'title' => "Service {$i}",
                'price' => 20.00,
                'image' => 'services/s.jpg',
                'status' => 1,
            ]);
        }

        $response = $this->getJson('/api/v1/services?per_page=2');

        $response->assertStatus(200)
            ->assertJsonPath('data.page', 1)
            ->assertJsonPath('data.total_page', 3)
            ->assertJsonPath('data.total_items', 5)
            ->assertJsonCount(2, 'data.items');
    }

    public function test_category_scoped_slug_uniqueness_and_auto_creation(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();

        $cat1 = ServiceCategory::create(['name' => 'Cat 1', 'slug' => 'cat-1']);
        $cat2 = ServiceCategory::create(['name' => 'Cat 2', 'slug' => 'cat-2']);

        // First service in Cat 1 -> 'hair-cut'
        $this->actingAs($user, 'sanctum')->postJson('/api/v1/services', [
            'service_category_id' => $cat1->id,
            'title' => 'Hair Cut',
            'price' => 30.00,
            'status' => 1,
            'image' => UploadedFile::fake()->image('s1.jpg'),
        ])->assertStatus(201)->assertJsonPath('data.slug', 'hair-cut');

        // Second service in Cat 1 (same title) -> 'hair-cut-2'
        $this->actingAs($user, 'sanctum')->postJson('/api/v1/services', [
            'service_category_id' => $cat1->id,
            'title' => 'Hair Cut',
            'price' => 30.00,
            'status' => 1,
            'image' => UploadedFile::fake()->image('s2.jpg'),
        ])->assertStatus(201)->assertJsonPath('data.slug', 'hair-cut-2');

        // Service in Cat 2 (same title) -> 'hair-cut' (allowed because category is different)
        $this->actingAs($user, 'sanctum')->postJson('/api/v1/services', [
            'service_category_id' => $cat2->id,
            'title' => 'Hair Cut',
            'price' => 30.00,
            'status' => 1,
            'image' => UploadedFile::fake()->image('s3.jpg'),
        ])->assertStatus(201)->assertJsonPath('data.slug', 'hair-cut');
    }
}
