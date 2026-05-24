<?php

namespace Tests\Unit;

use App\Services\MidtransService;
use Tests\TestCase;

class MidtransSignatureTest extends TestCase
{
    private const SERVER_KEY = 'SB-Mid-server-test-key';

    protected function setUp(): void
    {
        parent::setUp();
        config(['midtrans.server_key' => self::SERVER_KEY]);
    }

    public function test_signature_passes_when_key_matches(): void
    {
        $orderId = 'KDA-1-12345';
        $statusCode = '200';
        $grossAmount = '500000.00';

        $expected = hash('sha512', $orderId . $statusCode . $grossAmount . self::SERVER_KEY);

        $service = new MidtransService();

        $this->assertTrue($service->verifySignature([
            'order_id' => $orderId,
            'status_code' => $statusCode,
            'gross_amount' => $grossAmount,
            'signature_key' => $expected,
        ]));
    }

    public function test_signature_fails_when_key_is_tampered(): void
    {
        $service = new MidtransService();

        $this->assertFalse($service->verifySignature([
            'order_id' => 'KDA-1-12345',
            'status_code' => '200',
            'gross_amount' => '500000.00',
            'signature_key' => 'definitely-not-the-real-signature',
        ]));
    }

    public function test_signature_fails_when_signature_is_missing(): void
    {
        $service = new MidtransService();

        $this->assertFalse($service->verifySignature([
            'order_id' => 'KDA-1-12345',
            'status_code' => '200',
            'gross_amount' => '500000.00',
        ]));
    }

    public function test_signature_fails_when_required_fields_missing(): void
    {
        $service = new MidtransService();

        // Missing order_id → recomputed hash will differ from any valid signature.
        $this->assertFalse($service->verifySignature([
            'status_code' => '200',
            'gross_amount' => '500000.00',
            'signature_key' => str_repeat('a', 128),
        ]));
    }
}
