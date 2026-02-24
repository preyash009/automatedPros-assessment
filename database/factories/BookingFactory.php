<?php

namespace Database\Factories;

use App\Models\Booking;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class BookingFactory extends Factory
{
    protected $model = Booking::class;

    public function definition(): array
    {
        return [
            'user_id' => User::where('role', 'customer')->inRandomOrder()->first()->id ?? User::factory(),
            'ticket_id' => Ticket::factory(),
            'quantity' => fake()->numberBetween(1, 4),
            'status' => fake()->randomElement(['pending', 'confirmed', 'cancelled']),
        ];
    }
}
