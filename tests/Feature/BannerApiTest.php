<?php

namespace Tests\Feature;

use App\Models\Banner;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class BannerApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_list_banners_publicly(): void
    {
        Banner::create([
            'title'       => 'Spring Offer',
            'slug'        => 'spring-offer',
            'description' => 'Discounts up to 30%',
            'image'       => 'banners/spring.jpg',
            'status'      => 1,
        ]);

        $response = $this->getJson('/api/v1/banners');

        $response->assertStatus(200)
            ->assertJsonPath('data.items.0.title', 'Spring Offer')
            ->assertJsonPath('data.items.0.description', 'Discounts up to 30%');
    }

    public function test_can_create_banner_with_optional_description(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/banners', [
                'title'       => 'Special Event',
                'description' => 'Exclusive salon package deals',
                'status'      => 1,
                'image'       => UploadedFile::fake()->image('banner.jpg'),
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('title', 'Special Event')
            ->assertJsonPath('description', 'Exclusive salon package deals');

        $this->assertDatabaseHas('banners', [
            'title'       => 'Special Event',
            'description' => 'Exclusive salon package deals',
        ]);
    }

    public function test_can_update_banner_description(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();

        $banner = Banner::create([
            'title'       => 'Original Title',
            'description' => 'Original Description',
            'image'       => 'banners/orig.jpg',
            'status'      => 1,
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/banners/' . $banner->id, [
                '_method'     => 'PUT',
                'title'       => 'Updated Title',
                'description' => 'Updated Description details',
                'status'      => 1,
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('title', 'Updated Title')
            ->assertJsonPath('description', 'Updated Description details');

        $this->assertDatabaseHas('banners', [
            'id'          => $banner->id,
            'description' => 'Updated Description details',
        ]);
    }

    public function test_can_delete_banner(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();

        $banner = Banner::create([
            'title'       => 'To Delete',
            'description' => 'To be removed',
            'image'       => 'banners/delete.jpg',
            'status'      => 1,
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->deleteJson('/api/v1/banners/' . $banner->id);

        $response->assertStatus(200)
            ->assertJsonPath('message', 'Banner deleted successfully');

        $this->assertDatabaseMissing('banners', [
            'id' => $banner->id,
        ]);
    }
}
