<?php

namespace Database\Factories;

use App\Models\Booking;
use App\Models\Payment;
use Illuminate\Database\Eloquent\Factories\Factory;

class PaymentFactory extends Factory
{
    protected $model = Payment::class;

    public function definition(): array
    {
        $booking = Booking::factory()->create(['status' => 'confirmed']);

        return [
            'booking_id' => $booking->id,
            'user_id' => $booking->user_id,
            'amount' => $booking->quantity * ($booking->ticket->price ?? 100),
            'status' => 'success',
        ];
    }
}
