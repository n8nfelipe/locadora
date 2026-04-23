<?php

namespace App\Services;

use App\Models\Payment;
use App\Models\Rental;
use App\Repositories\PaymentRepository;
use Illuminate\Support\Facades\DB;

class PaymentService
{
    public function __construct(
        protected PaymentRepository $repository
    ) {
    }

    public function pay(Rental $rental, string $method): Payment
    {
        return DB::transaction(function () use ($rental, $method) {
            $success = true;

            return $this->repository->create([
                'rental_id' => $rental->id,
                'amount' => $rental->total_price,
                'payment_method' => $method,
                'status' => $success ? 'completed' : 'failed',
                'paid_at' => $success ? now() : null,
            ]);
        });
    }
}
