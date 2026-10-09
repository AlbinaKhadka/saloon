<?php

namespace Tests\Feature;

use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\Stylist;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class StylistApiTest extends TestCase
{
    public function test_can_instantiate_stylist_model(): void
    {
        $stylist = new Stylist([
            'name'             => 'Jane Doe',
            'designation'      => 'Senior Stylist',
            'bio'              => 'Expert in hair styling',
            'status'           => 1,
            'experience_years' => 5,
        ]);

        $this::assertEquals('Jane Doe', $stylist->name);
        $this::assertEquals('Senior Stylist', $stylist->designation);
        $this::assertEquals(1, $stylist->status);
    }
}
