<?php

namespace Tests\Unit;

use App\Services\PaymentService;
use PHPUnit\Framework\TestCase;

class PaymentServiceTest extends TestCase
{
    /**
     * Test the process method of PaymentService.
     */
    public function test_payment_service_returns_valid_structure()
    {
        $service = new PaymentService();
        $result = $service->process(100.0);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('success', $result);
        $this->assertArrayHasKey('transaction_id', $result);
        $this->assertArrayHasKey('message', $result);
        $this->assertIsBool($result['success']);
    }

    /**
     * Test logic consistency (running multiple times to ensure structure holds for both success and failure).
     */
    public function test_payment_service_handles_multiple_iterations()
    {
        $service = new PaymentService();

        for ($i = 0; $i < 20; $i++) {
            $result = $service->process(50.0);

            if ($result['success']) {
                $this->assertNotNull($result['transaction_id']);
                $this->assertStringContainsString('TXN-', $result['transaction_id']);
            } else {
                $this->assertNull($result['transaction_id']);
            }

            $this->assertIsString($result['message']);
        }
    }
}
