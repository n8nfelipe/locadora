<?php

namespace Tests\Unit;

use App\Models\Payment;
use App\Models\Rental;
use App\Repositories\PaymentRepository;
use App\Services\PaymentService;
use Illuminate\Support\Facades\DB;
use Mockery;
use Tests\TestCase;

class PaymentServiceTest extends TestCase
{
    protected PaymentService $paymentService;
    protected $repositoryMock;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repositoryMock = Mockery::mock(PaymentRepository::class);
        $this->paymentService = new PaymentService($this->repositoryMock);
    }

    public function test_pay_creates_payment_record()
    {
        $rental = new Rental(['id' => 'rent-1', 'total_price' => 200.0]);
        $payment = new Payment(['id' => 'pay-1']);

        $this->repositoryMock->shouldReceive('create')
            ->once()
            ->andReturn($payment);

        DB::shouldReceive('transaction')->once()->andReturnUsing(fn($callback) => $callback());

        $result = $this->paymentService->pay($rental, 'credit_card');

        $this->assertEquals($payment, $result);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
