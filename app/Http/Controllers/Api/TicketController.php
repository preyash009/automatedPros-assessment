<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Ticket;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TicketController extends Controller
{
    use ApiResponse;

    /**
     * Store a newly created resource in storage (for a specific event).
     */
    public function store(Request $request, $event_id)
    {
        $event = Event::find($event_id);

        if (!$event) {
            return $this->error('Event not found', 404);
        }

        // Permission check
        if (!$request->user()->can('create_tickets')) {
            return $this->error('You do not have permission to add tickets', 403);
        }

        // Ownership check
        if ($request->user()->role !== 'admin' && $event->created_by !== $request->user()->id) {
            return $this->error('You are not authorized to add tickets to this event', 403);
        }

        $validator = Validator::make($request->all(), [
            'type' => 'required|string|max:255', // VIP, Standard, etc.
            'price' => 'required|numeric|min:0',
            'quantity' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return $this->error('Validation Error', 422, $validator->errors());
        }

        $ticket = $event->tickets()->create($request->all());

        return $this->success($ticket, 'Ticket created successfully', 201);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $ticket = Ticket::with('event')->find($id);

        if (!$ticket) {
            return $this->error('Ticket not found', 404);
        }

        // Permission check
        if (!$request->user()->can('edit_tickets')) {
            return $this->error('You do not have permission to edit tickets', 403);
        }

        // Ownership check
        if ($request->user()->role !== 'admin' && $ticket->event->created_by !== $request->user()->id) {
            return $this->error('You are not authorized to update this ticket', 403);
        }

        if (empty($request->only(['type', 'price', 'quantity']))) {
            return $this->error('At least one field (type, price, or quantity) is required for update.', 422);
        }

        $validator = Validator::make($request->all(), [
            'type' => 'sometimes|required|string|max:255',
            'price' => 'sometimes|required|numeric|min:0',
            'quantity' => 'sometimes|required|integer|min:0',
        ]);

        if ($validator->fails()) {
            return $this->error('Validation Error', 422, $validator->errors());
        }

        $ticket->update($request->all());

        return $this->success($ticket, 'Ticket updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, $id)
    {
        $ticket = Ticket::with('event')->find($id);

        if (!$ticket) {
            return $this->error('Ticket not found', 404);
        }

        // Permission check
        if (!$request->user()->can('delete_tickets')) {
            return $this->error('You do not have permission to delete tickets', 403);
        }

        // Ownership check
        if ($request->user()->role !== 'admin' && $ticket->event->created_by !== $request->user()->id) {
            return $this->error('You are not authorized to delete this ticket', 403);
        }

        $ticket->delete();

        return $this->success(null, 'Ticket deleted successfully');
    }
}
