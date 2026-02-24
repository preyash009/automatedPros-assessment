<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Payment;
use App\Traits\ApiResponse;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Notifications\BookingConfirmed;

class PaymentController extends Controller
{
    use ApiResponse;

    /**
     * Store a mock payment for a booking.
     */
    public function store(Request $request, $booking_id, PaymentService $paymentService)
    {
        $booking = Booking::with('ticket')->find($booking_id);

        if (!$booking) {
            return $this->error('Booking not found', 404);
        }

        if ($booking->user_id !== $request->user()->id) {
            // Permission check: Admin or authorized users might want to pay? 
            // Usually only the user who booked can pay, so we'll keep ownership check tight.
            // But we can add a permission check if needed.
            if (!$request->user()->can('manage_payments')) {
                return $this->error('You are not authorized to make payment for this booking', 403);
            }
        }

        if ($booking->status !== 'pending') {
            return $this->error('Payment can only be made for pending bookings', 400);
        }

        $amount = (float)($booking->quantity * $booking->ticket->price);

        $result = $paymentService->process($amount);

        if (!$result['success']) {
            return $this->error($result['message'], 402);
        }

        try {
            return DB::transaction(function () use ($booking, $amount, $request) {
                // Create Payment Record
                $payment = Payment::create([
                    'user_id' => $request->user()->id,
                    'booking_id' => $booking->id,
                    'amount' => $amount,
                    'status' => 'success',
                ]);

                $booking->update(['status' => 'confirmed']);

                $request->user()->notify(new BookingConfirmed($booking->load('ticket.event')));

                return $this->success($payment, 'Payment processed successfully. Your booking is now confirmed.', 201);
            });
        } catch (\Exception $e) {
            return $this->error('Internal processing failed: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, $id)
    {
        $payment = Payment::with('booking.ticket.event')->find($id);

        if (!$payment) {
            return $this->error('Payment record not found', 404);
        }

        // Permission check
        if (!$request->user()->can('view_payments')) {
            return $this->error('You do not have permission to view payments', 403);
        }

        // Ownership check: Customer only their own, Admin can view any
        if ($request->user()->role !== 'admin' && $payment->user_id !== $request->user()->id) {
            return $this->error('You are not authorized to view this payment', 403);
        }

        return $this->success($payment, 'Payment details retrieved successfully');
    }
}
