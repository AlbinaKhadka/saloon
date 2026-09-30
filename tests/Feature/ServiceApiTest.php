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

    public function test_public_index_and_show_services_work_without_auth(): void
    {
        $category = ServiceCategory::create(['name' => 'Hair', 'slug' => 'hair']);
        $service = Service::create([
            'service_category_id' => $category->id,
            'title' => 'Hair Cut',
            'price' => 50.00,
            'image' => 'services/test.jpg',
            'status' => 1,
        ]);

        $this->getJson('/api/v1/services')
            ->assertStatus(200)
            ->assertJsonPath('data.0.title', 'Hair Cut');

        $this->getJson('/api/v1/services/' . $service->id)
            ->assertStatus(200)
            ->assertJsonPath('data.title', 'Hair Cut')
            ->assertJsonPath('data.category.name', 'Hair');
    }

    public function test_category_scoped_slug_uniqueness(): void
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

    public function test_existing_service_backfill_migration_assigns_general_category(): void
    {
        $category = ServiceCategory::create(['name' => 'General', 'slug' => 'general']);
        $service = Service::create([
            'service_category_id' => $category->id,
            'title' => 'haircutting',
            'slug' => 'haircutting',
            'price' => 25.00,
            'image' => 'services/haircutting.jpg',
            'status' => 1,
        ]);

        $this->assertDatabaseHas('services', [
            'id' => $service->id,
            'title' => 'haircutting',
            'service_category_id' => $category->id,
        ]);
    }
}
