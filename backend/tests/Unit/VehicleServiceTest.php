<?php

namespace Tests\Unit;

use App\Models\Vehicle;
use App\Repositories\VehicleRepository;
use App\Services\VehicleService;
use Illuminate\Support\Facades\Cache;
use Mockery;
use Tests\TestCase;

class VehicleServiceTest extends TestCase
{
    protected VehicleService $vehicleService;
    protected $repositoryMock;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repositoryMock = Mockery::mock(VehicleRepository::class);
        $this->vehicleService = new VehicleService($this->repositoryMock);
    }

    public function test_get_all_calls_repository()
    {
        $filters = ['category_id' => 'uuid-1'];
        $this->repositoryMock->shouldReceive('all')->once()->with($filters)->andReturn(collect());
        
        $result = $this->vehicleService->getAll($filters);
        $this->assertCount(0, $result);
    }

    public function test_create_vehicle_and_clears_cache()
    {
        $data = ['brand' => 'Toyota'];
        $vehicle = new Vehicle($data);

        $this->repositoryMock->shouldReceive('create')->once()->with($data)->andReturn($vehicle);
        Cache::shouldReceive('forget')->once()->with('available_vehicles');

        $result = $this->vehicleService->create($data);
        $this->assertEquals($vehicle, $result);
    }

    public function test_update_vehicle_and_clears_cache()
    {
        $vehicle = new Vehicle();
        $data = ['brand' => 'Honda'];

        $this->repositoryMock->shouldReceive('update')->once()->with($vehicle, $data)->andReturn($vehicle);
        Cache::shouldReceive('forget')->once()->with('available_vehicles');

        $result = $this->vehicleService->update($vehicle, $data);
        $this->assertEquals($vehicle, $result);
    }

    public function test_delete_vehicle_and_clears_cache()
    {
        $vehicle = new Vehicle();

        $this->repositoryMock->shouldReceive('delete')->once()->with($vehicle);
        Cache::shouldReceive('forget')->once()->with('available_vehicles');

        $this->vehicleService->delete($vehicle);
        $this->assertTrue(true);
    }

    public function test_get_available_vehicles_uses_cache()
    {
        $vehicles = collect([new Vehicle(['brand' => 'Toyota'])]);

        Cache::shouldReceive('remember')
            ->once()
            ->with('available_vehicles', 3600, Mockery::any())
            ->andReturn($vehicles);

        $result = $this->vehicleService->getAvailable();
        $this->assertEquals($vehicles, $result);
    }

    public function test_get_available_vehicles_handles_cache_miss()
    {
        $vehicles = collect([new Vehicle(['brand' => 'Toyota'])]);

        $this->repositoryMock->shouldReceive('findAvailable')->once()->andReturn($vehicles);

        Cache::shouldReceive('remember')
            ->once()
            ->with('available_vehicles', 3600, Mockery::any())
            ->andReturnUsing(function($key, $ttl, $callback) {
                return $callback();
            });

        $result = $this->vehicleService->getAvailable();
        $this->assertEquals($vehicles, $result);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
