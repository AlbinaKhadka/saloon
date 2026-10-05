<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Banner;
use App\Models\ServiceCategory;
use App\Models\Service;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthPasswordAndSearchTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_change_password(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('oldpassword123'),
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/change-password', [
                'current_password'          => 'oldpassword123',
                'new_password'              => 'newpassword123',
                'new_password_confirmation' => 'newpassword123',
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('message', 'Password changed successfully');

        $this->assertTrue(Hash::check('newpassword123', $user->fresh()->password));
    }

    public function test_change_password_fails_if_current_password_incorrect(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('realpassword123'),
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/change-password', [
                'current_password'          => 'wrongpassword',
                'new_password'              => 'newpassword123',
                'new_password_confirmation' => 'newpassword123',
            ]);

        $response->assertStatus(422)
            ->assertJsonPath('message', 'Current password does not match');
    }

    public function test_banner_search_by_title(): void
    {
        Banner::create(['title' => 'Summer Special', 'status' => 1, 'image' => 'b1.jpg']);
        Banner::create(['title' => 'Winter Sale', 'status' => 1, 'image' => 'b2.jpg']);

        $response = $this->getJson('/api/v1/banners?search=Summer');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data.items')
            ->assertJsonPath('data.items.0.title', 'Summer Special');
    }

    public function test_service_category_search_by_title(): void
    {
        ServiceCategory::create(['name' => 'Hair Styling', 'status' => true]);
        ServiceCategory::create(['name' => 'Nail Art', 'status' => true]);

        $response = $this->getJson('/api/v1/service-categories?search=Hair');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data.items')
            ->assertJsonPath('data.items.0.name', 'Hair Styling');
    }

    public function test_service_search_by_title_and_category(): void
    {
        $cat1 = ServiceCategory::create(['name' => 'Hair', 'slug' => 'hair']);
        $cat2 = ServiceCategory::create(['name' => 'Spa', 'slug' => 'spa']);

        Service::create([
            'service_category_id' => $cat1->id,
            'title' => 'Hair Cut',
            'price' => 25.00,
            'image' => 's1.jpg',
            'status' => 1,
        ]);

        Service::create([
            'service_category_id' => $cat2->id,
            'title' => 'Hot Stone Massage',
            'price' => 80.00,
            'image' => 's2.jpg',
            'status' => 1,
        ]);

        $response = $this->getJson('/api/v1/services?search=Cut&service_category_id=' . $cat1->id);

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data.items')
            ->assertJsonPath('data.items.0.title', 'Hair Cut');
    }
}
