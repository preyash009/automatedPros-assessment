<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Database\Seeders\RolePermissionSeeder;

class EventTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
    }

    public function test_organizer_can_create_event()
    {
        $organizer = User::factory()->create(['role' => 'organizer']);
        $organizer->assignRole('organizer');

        $response = $this->actingAs($organizer, 'sanctum')->postJson('/api/events', [
            'title' => 'Sample Event',
            'description' => 'Event description',
            'date' => now()->addWeek()->format('Y-m-d H:i:s'),
            'location' => 'San Francisco'
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('events', ['title' => 'Sample Event']);
    }

    public function test_customer_cannot_create_event()
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $customer->assignRole('customer');

        $response = $this->actingAs($customer, 'sanctum')->postJson('/api/events', [
            'title' => 'Hacker Event',
            'description' => 'Event description',
            'date' => now()->addWeek()->format('Y-m-d H:i:s'),
            'location' => 'Unknown'
        ]);

        $response->assertStatus(403);
    }
}
