<?php

namespace Database\Factories;

use App\Models\Event;
use App\Models\Ticket;
use Illuminate\Database\Eloquent\Factories\Factory;

class TicketFactory extends Factory
{
    protected $model = Ticket::class;

    public function definition(): array
    {
        return [
            'type' => fake()->randomElement(['VIP', 'Standard', 'Early Bird', 'Economy']),
            'price' => fake()->randomFloat(2, 20, 500),
            'quantity' => fake()->numberBetween(50, 200),
            'event_id' => Event::factory(),
        ];
    }
}
