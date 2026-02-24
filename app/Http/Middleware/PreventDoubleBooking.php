<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PreventDoubleBooking
{
    use \App\Traits\ApiResponse;

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $ticketId = $request->route('id');
        $userId = $request->user()->id;

        $exists = \App\Models\Booking::where('user_id', $userId)
            ->where('ticket_id', $ticketId)
            ->whereIn('status', ['pending', 'confirmed'])
            ->exists();

        if ($exists) {
            return $this->error('You already have an active booking for this ticket.', 400);
        }

        return $next($request);
    }
}
