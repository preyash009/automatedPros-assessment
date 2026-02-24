<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BookingConfirmed extends Notification implements ShouldQueue
{
    use Queueable;

    protected $booking;

    /**
     * Create a new notification instance.
     */
    public function __construct($booking)
    {
        $this->booking = $booking;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Booking Confirmed: ' . $this->booking->ticket->event->title)
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('Your booking for the event "' . $this->booking->ticket->event->title . '" has been successfully confirmed.')
            ->line('Ticket Type: ' . $this->booking->ticket->type)
            ->line('Quantity: ' . $this->booking->quantity)
            ->line('Total Amount: ' . $this->booking->quantity * $this->booking->ticket->price)
            ->action('View My Bookings', url('/api/bookings'))
            ->line('Thank you for booking with us!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'booking_id' => $this->booking->id,
            'event_title' => $this->booking->ticket->event->title,
            'amount' => $this->booking->quantity * $this->booking->ticket->price,
            'message' => 'Your booking has been confirmed.'
        ];
    }
}
