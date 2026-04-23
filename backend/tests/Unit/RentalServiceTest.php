<?php

namespace Tests\Unit;

use App\Models\Rental;
use App\Models\Reservation;
use App\Models\Vehicle;
use App\Models\VehicleCategory;
use App\Repositories\RentalRepository;
use App\Services\PricingService;
use App\Services\RentalService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Mockery;
use Tests\TestCase;

class RentalServiceTest extends TestCase
{
    protected RentalService $rentalService;
    protected $repositoryMock;
    protected $pricingServiceMock;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repositoryMock = Mockery::mock(RentalRepository::class);
        $this->pricingServiceMock = Mockery::mock(PricingService::class);
        $this->rentalService = new RentalService($this->repositoryMock, $this->pricingServiceMock);
    }

    public function test_checkout_creates_rental_success()
    {
        $vehicle = Mockery::mock(Vehicle::class)->makePartial();
        $vehicle->status = 'available';
        $vehicle->shouldReceive('update')->once()->with(['status' => 'rented']);

        $reservation = Mockery::mock(Reservation::class)->makePartial();
        $reservation->id = 'res-1';
        $reservation->status = 'active';
        $reservation->start_date = Carbon::now();
        $reservation->end_date = Carbon::now()->addDays(2);
        $reservation->vehicle = $vehicle;
        $reservation->shouldReceive('update')->once()->with(['status' => 'completed']);

        $this->pricingServiceMock->shouldReceive('calculate')->once()->andReturn(200.0);
        $rental = new Rental(['id' => 'rent-1']);
        $this->repositoryMock->shouldReceive('create')->once()->andReturn($rental);

        DB::shouldReceive('transaction')->once()->andReturnUsing(fn($callback) => $callback());

        $result = $this->rentalService->checkout($reservation);
        $this->assertEquals($rental, $result);
    }

    public function test_checkout_fails_if_reservation_not_active()
    {
        $reservation = new Reservation(['status' => 'cancelled']);
        DB::shouldReceive('transaction')->once()->andReturnUsing(fn($callback) => $callback());

        $this->expectException(ValidationException::class);
        $this->rentalService->checkout($reservation);
    }

    public function test_checkout_fails_if_vehicle_not_available()
    {
        $vehicle = new Vehicle(['status' => 'rented']);
        $reservation = Mockery::mock(Reservation::class)->makePartial();
        $reservation->status = 'active';
        $reservation->vehicle = $vehicle;

        DB::shouldReceive('transaction')->once()->andReturnUsing(fn($callback) => $callback());

        $this->expectException(ValidationException::class);
        $this->rentalService->checkout($reservation);
    }

    public function test_checkin_success_no_late_fees()
    {
        $category = new VehicleCategory(['daily_rate' => 100]);
        $vehicle = Mockery::mock(Vehicle::class)->makePartial();
        $vehicle->category = $category;
        $vehicle->shouldReceive('update')->once()->with(['status' => 'available']);

        $rental = Mockery::mock(Rental::class)->makePartial();
        $rental->status = 'ongoing';
        $rental->end_date = Carbon::now()->addDays(1);
        $rental->total_price = 100.0;
        $rental->vehicle = $vehicle;

        $this->repositoryMock->shouldReceive('update')->once()->andReturnUsing(function($r, $data) {
            $r->fill($data);
            return $r;
        });

        DB::shouldReceive('transaction')->once()->andReturnUsing(fn($callback) => $callback());

        $result = $this->rentalService->checkin($rental);
        $this->assertEquals('finished', $result->status);
    }

    public function test_checkin_with_late_fees()
    {
        $category = new VehicleCategory(['daily_rate' => 100]);
        $vehicle = Mockery::mock(Vehicle::class)->makePartial();
        $vehicle->category = $category;
        $vehicle->shouldReceive('update')->once()->with(['status' => 'available']);

        $rental = Mockery::mock(Rental::class)->makePartial();
        $rental->status = 'ongoing';
        $rental->end_date = Carbon::now()->subDays(2);
        $rental->total_price = 100.0;
        $rental->vehicle = $vehicle;

        $this->pricingServiceMock->shouldReceive('calculateLateFees')->once()->andReturn(240.0);

        $this->repositoryMock->shouldReceive('update')->once()->andReturnUsing(function($r, $data) {
            $r->fill($data);
            return $r;
        });

        DB::shouldReceive('transaction')->once()->andReturnUsing(fn($callback) => $callback());

        $result = $this->rentalService->checkin($rental);
        $this->assertEquals('late', $result->status);
        $this->assertEquals(340.0, $result->total_price);
    }

    public function test_checkin_returns_early_if_not_ongoing()
    {
        $rental = new Rental(['status' => 'finished']);
        DB::shouldReceive('transaction')->once()->andReturnUsing(fn($callback) => $callback());

        $result = $this->rentalService->checkin($rental);
        $this->assertEquals('finished', $result->status);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
