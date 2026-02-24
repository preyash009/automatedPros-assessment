<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Ticket;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class BookingController extends Controller
{
    use ApiResponse;

    /**
     * Display a listing of the customer's bookings.
     */
    public function index(Request $request)
    {
        if (!$request->user()->can('view_bookings')) {
            return $this->error('You do not have permission to view bookings', 403);
        }
        $bookings = Booking::where('user_id', $request->user()->id)
            ->with(['ticket.event', 'payment'])
            ->get();

        return $this->success($bookings, 'Your bookings retrieved successfully');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, $ticket_id)
    {
        $ticket = Ticket::with('event')->find($ticket_id);

        if (!$ticket) {
            return $this->error('Ticket not found', 404);
        }

        $validator = Validator::make($request->all(), [
            'quantity' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return $this->error('Validation Error', 422, $validator->errors());
        }

        if (!$request->user()->can('create_bookings')) {
            return $this->error('You do not have permission to create bookings', 403);
        }

        if ($ticket->quantity < $request->quantity) {
            return $this->error('Not enough tickets available', 400);
        }

        try {
            return DB::transaction(function () use ($request, $ticket) {
                // Decrement ticket quantity
                $ticket->decrement('quantity', $request->quantity);

                // Create booking
                $booking = Booking::create([
                    'user_id' => $request->user()->id,
                    'ticket_id' => $ticket->id,
                    'quantity' => $request->quantity,
                    'status' => 'pending',
                ]);

                return $this->success($booking->load('ticket.event'), 'Booking created successfully. Please proceed to payment.', 201);
            });
        } catch (\Exception $e) {
            return $this->error('Could not create booking: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Cancel a booking.
     */
    public function cancel(Request $request, $id)
    {
        $booking = Booking::with('ticket')->find($id);

        if (!$booking) {
            return $this->error('Booking not found', 404);
        }

        // Permission check
        if (!$request->user()->can('delete_bookings')) {
            return $this->error('You do not have permission to cancel bookings', 403);
        }

        // Ownership check: Customer can only cancel their own
        if ($request->user()->role !== 'admin' && $booking->user_id !== $request->user()->id) {
            return $this->error('You are not authorized to cancel this booking', 403);
        }

        if ($booking->status === 'cancelled') {
            return $this->error('Booking is already cancelled', 400);
        }

        try {
            return DB::transaction(function () use ($booking) {
                // Restore ticket quantity
                $booking->ticket->increment('quantity', $booking->quantity);

                // Update status
                $booking->update(['status' => 'cancelled']);

                return $this->success($booking, 'Booking cancelled successfully and ticket inventory restored');
            });
        } catch (\Exception $e) {
            return $this->error('Could not cancel booking: ' . $e->getMessage(), 500);
        }
    }
}
