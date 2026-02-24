<x-mail::message>
# Booking Confirmed

Hello {{ $booking->user->name }},

Your booking for the event **{{ $booking->ticket->event->title }}** has been confirmed!

**Booking Details:**
- **Ticket Type:** {{ $booking->ticket->type }}
- **Quantity:** {{ $booking->quantity }}
- **Total Paid:** ${{ number_format($booking->payment->amount ?? 0, 2) }}

<x-mail::button :url="config('app.url')">
View Booking
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
