<?php

namespace App\Console\Commands;

use App\Services\ReservationService;
use Illuminate\Console\Command;

class ExpireReservations extends Command
{
    protected $signature = 'reservations:expire';
    protected $description = 'Expire active reservations older than 2 hours';

    public function handle(ReservationService $reservationService)
    {
        $reservationService->expireReservations();
        $this->info('Expired reservations processed successfully.');
    }
}
