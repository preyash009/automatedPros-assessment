<?php

namespace Database\Seeders;

use App\Models\Booking;
use App\Models\Event;
use App\Models\Ticket;
use App\Models\User;
use App\Models\Payment;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class DemoDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admins = User::factory()->count(2)->create(['role' => 'admin']);
        foreach ($admins as $user) {
            $user->assignRole(Role::findByName('admin', 'api'));
        }

        $organizers = User::factory()->count(3)->create(['role' => 'organizer']);
        foreach ($organizers as $user) {
            $user->assignRole(Role::findByName('organizer', 'api'));
        }

        $customers = User::factory()->count(10)->create(['role' => 'customer']);
        foreach ($customers as $user) {
            $user->assignRole(Role::findByName('customer', 'api'));
        }

        $events = Event::factory()->count(5)->sequence(fn($sq) => [
            'created_by' => $organizers->random()->id
        ])->create();

        $tickets = collect();
        foreach ($events as $event) {
            $eventTickets = Ticket::factory()->count(3)->create([
                'event_id' => $event->id
            ]);
            $tickets = $tickets->concat($eventTickets);
        }

        for ($i = 0; $i < 20; $i++) {
            $ticket = $tickets->random();
            $customer = $customers->random();
            $quantity = rand(1, 4);

            if ($ticket->quantity >= $quantity) {
                $status = fake()->randomElement(['pending', 'confirmed', 'cancelled']);

                $booking = Booking::create([
                    'user_id' => $customer->id,
                    'ticket_id' => $ticket->id,
                    'quantity' => $quantity,
                    'status' => $status
                ]);

                // If confirmed, create a payment entry
                if ($status === 'confirmed') {
                    Payment::create([
                        'user_id' => $customer->id,
                        'booking_id' => $booking->id,
                        'amount' => $quantity * $ticket->price,
                        'status' => 'success'
                    ]);
                }

                $ticket->decrement('quantity', $quantity);
            }
        }
    }
}
