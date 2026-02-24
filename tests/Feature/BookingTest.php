<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Database\Seeders\RolePermissionSeeder;

class BookingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
    }

    public function test_customer_can_book_ticket()
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $customer->assignRole('customer');

        $organizer = User::factory()->create(['role' => 'organizer']);
        $event = Event::factory()->create(['created_by' => $organizer->id]);
        $ticket = Ticket::factory()->create([
            'event_id' => $event->id,
            'quantity' => 10,
            'price' => 50
        ]);

        $response = $this->actingAs($customer, 'sanctum')->postJson("/api/tickets/{$ticket->id}/bookings", [
            'quantity' => 2
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('bookings', [
            'user_id' => $customer->id,
            'ticket_id' => $ticket->id,
            'quantity' => 2,
            'status' => 'pending'
        ]);

        // Check inventory reduction
        $this->assertEquals(8, $ticket->fresh()->quantity);
    }

    public function test_customer_cannot_double_book_active_ticket()
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $customer->assignRole('customer');

        $organizer = User::factory()->create(['role' => 'organizer']);
        $event = Event::factory()->create(['created_by' => $organizer->id]);
        $ticket = Ticket::factory()->create(['event_id' => $event->id]);

        // First booking
        $this->actingAs($customer, 'sanctum')->postJson("/api/tickets/{$ticket->id}/bookings", ['quantity' => 1]);

        // Second booking (should be blocked by middleware)
        $response = $this->actingAs($customer, 'sanctum')->postJson("/api/tickets/{$ticket->id}/bookings", ['quantity' => 1]);

        $response->assertStatus(400);
        $response->assertJsonFragment(['message' => 'You already have an active booking for this ticket.']);
    }
}
