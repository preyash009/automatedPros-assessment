<?php

namespace App\Services;

class PaymentService
{
    /**
     * Simulate a payment process.
     *
     * @param float $amount
     * @return array
     */
    public function process(float $amount): array
    {
        // Randomly simulate success (95%) or failure (5%)
        $isSuccess = rand(1, 100) <= 95;

        if ($isSuccess) {
            return [
                'success' => true,
                'transaction_id' => 'TXN-' . strtoupper(uniqid()),
                'message' => 'Payment processed successfully.',
            ];
        }

        return [
            'success' => false,
            'transaction_id' => null,
            'message' => 'Payment failed due to insufficient funds or network error.',
        ];
    }
}
