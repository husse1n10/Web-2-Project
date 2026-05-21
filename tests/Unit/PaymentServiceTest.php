<?php

namespace Tests\Unit;

use App\Services\PaymentService;
use PHPUnit\Framework\TestCase;

class PaymentServiceTest extends TestCase
{
    public function test_to_minor_unit_rounds_half_up_consistently(): void
    {
        $this->assertSame(1099, PaymentService::toMinorUnit('10.99'));
        $this->assertSame(1010, PaymentService::toMinorUnit('10.10'));
        $this->assertSame(1100, PaymentService::toMinorUnit('10.995'));
    }
}
