<?php

namespace Tests\Unit;

use App\Models\Reservation;
use App\Models\Vehicle;
use App\Repositories\ReservationRepository;
use App\Repositories\VehicleRepository;
use App\Services\ReservationService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Mockery;
use Tests\TestCase;

class ReservationServiceTest extends TestCase
{
    protected ReservationService $reservationService;
    protected $repositoryMock;
    protected $vehicleRepositoryMock;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repositoryMock = Mockery::mock(ReservationRepository::class);
        $this->vehicleRepositoryMock = Mockery::mock(VehicleRepository::class);
        $this->reservationService = new ReservationService($this->repositoryMock, $this->vehicleRepositoryMock);
    }

    public function test_create_reservation_success()
    {
        $data = [
            'vehicle_id' => 'v-1',
            'user_id' => 'u-1',
            'start_date' => '2024-01-01',
            'end_date' => '2024-01-05'
        ];

        $vehicle = new Vehicle(['status' => 'available']);

        $this->vehicleRepositoryMock->shouldReceive('findById')->once()->with('v-1')->andReturn($vehicle);
        
        $reservation = new Reservation();
        $this->repositoryMock->shouldReceive('create')->once()->andReturn($reservation);

        DB::shouldReceive('transaction')->once()->andReturnUsing(fn($callback) => $callback());

        $result = $this->reservationService->create($data);
        $this->assertEquals($reservation, $result);
    }

    public function test_create_reservation_fails_when_vehicle_not_available()
    {
        $data = ['vehicle_id' => 'v-1'];
        $vehicle = new Vehicle(['status' => 'rented']);

        $this->vehicleRepositoryMock->shouldReceive('findById')->once()->with('v-1')->andReturn($vehicle);
        DB::shouldReceive('transaction')->once()->andReturnUsing(fn($callback) => $callback());

        $this->expectException(ValidationException::class);
        $this->reservationService->create($data);
    }

    public function test_expire_reservations_calls_repository()
    {
        $this->repositoryMock->shouldReceive('expireOld')->once()->with(2);
        $this->reservationService->expireReservations();
        $this->assertTrue(true);
    }

    public function test_cancel_reservation_calls_repository()
    {
        $reservation = new Reservation();
        $this->repositoryMock->shouldReceive('update')->once()->with($reservation, ['status' => 'cancelled']);
        $this->reservationService->cancel($reservation);
        $this->assertTrue(true);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
