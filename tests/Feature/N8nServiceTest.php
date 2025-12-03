<?php

namespace Tests\Feature;

use App\Services\N8nService;
use Tests\TestCase;

class N8nServiceTest extends TestCase
{
    private N8nService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(N8nService::class);
    }

    public function test_generates_consistent_hmac_signature(): void
    {
        $payload = ['test' => 'data'];

        $sig1 = $this->service->generateSignature($payload);
        $sig2 = $this->service->generateSignature($payload);

        $this->assertEquals($sig1, $sig2);
    }

    public function test_generates_different_signatures_for_different_payloads(): void
    {
        $payload1 = ['test' => 'data1'];
        $payload2 = ['test' => 'data2'];

        $sig1 = $this->service->generateSignature($payload1);
        $sig2 = $this->service->generateSignature($payload2);

        $this->assertNotEquals($sig1, $sig2);
    }

    public function test_verifies_valid_signature(): void
    {
        $payload = ['test' => 'data'];
        $signature = $this->service->generateSignature($payload);

        $result = $this->service->verifySignature($signature, $payload);

        $this->assertTrue($result);
    }

    public function test_rejects_invalid_signature(): void
    {
        $payload = ['test' => 'data'];
        $invalidSignature = 'invalid-signature-here';

        $result = $this->service->verifySignature($invalidSignature, $payload);

        $this->assertFalse($result);
    }

    public function test_rejects_tampered_payload(): void
    {
        $originalPayload = ['test' => 'original'];
        $signature = $this->service->generateSignature($originalPayload);

        $tamperedPayload = ['test' => 'tampered'];
        $result = $this->service->verifySignature($signature, $tamperedPayload);

        $this->assertFalse($result);
    }
}
