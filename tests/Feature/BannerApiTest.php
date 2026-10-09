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
            ->assertJsonPath('data.items.0.slug', 'spring-offer')
            ->assertJsonPath('data.items.0.description', 'Discounts up to 30%');
    }

    public function test_slug_is_auto_generated_from_title_on_creation(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/banners', [
                'title'       => 'Grand Opening Sale',
                'description' => 'Discounts for all first time customers',
                'status'      => 1,
                'image'       => UploadedFile::fake()->image('banner.jpg'),
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('title', 'Grand Opening Sale')
            ->assertJsonPath('slug', 'grand-opening-sale')
            ->assertJsonPath('description', 'Discounts for all first time customers');

        $this->assertDatabaseHas('banners', [
            'title' => 'Grand Opening Sale',
            'slug'  => 'grand-opening-sale',
        ]);
    }

    public function test_banner_can_be_created_without_title(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/banners', [
                'status' => 1,
                'image'  => UploadedFile::fake()->image('banner.jpg'),
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('title', null)
            ->assertJsonPath('slug', null);

        $this->assertDatabaseHas('banners', [
            'status' => 1,
            'title'  => null,
            'slug'   => null,
        ]);
    }

    public function test_slug_auto_updates_when_title_changes(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();

        $banner = Banner::create([
            'title'       => 'Initial Title',
            'description' => 'Initial Description',
            'image'       => 'banners/orig.jpg',
            'status'      => 1,
        ]);

        $this->assertEquals('initial-title', $banner->slug);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/banners/' . $banner->id, [
                '_method'     => 'PUT',
                'title'       => 'Updated Special Title',
                'description' => 'Updated Description',
                'status'      => 1,
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('title', 'Updated Special Title')
            ->assertJsonPath('slug', 'updated-special-title');

        $this->assertDatabaseHas('banners', [
            'id'    => $banner->id,
            'title' => 'Updated Special Title',
            'slug'  => 'updated-special-title',
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
