<?php

namespace Tests\Unit;

use App\Models\User;
use App\Repositories\UserRepository;
use App\Services\AuthService;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Laravel\Sanctum\PersonalAccessToken;
use Mockery;
use Tests\TestCase;

class AuthServiceTest extends TestCase
{
    protected AuthService $authService;
    protected $repositoryMock;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repositoryMock = Mockery::mock(UserRepository::class);
        $this->authService = new AuthService($this->repositoryMock);
    }

    public function test_register_creates_user_and_returns_token()
    {
        $userData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password',
        ];

        Hash::shouldReceive('make')->once()->andReturn('hashed_password');
        
        $user = Mockery::mock(User::class)->makePartial();
        $user->shouldReceive('createToken')->andReturn((object)['plainTextToken' => 'test_token']);

        $this->repositoryMock->shouldReceive('create')
            ->once()
            ->andReturn($user);

        $result = $this->authService->register($userData);

        $this->assertEquals('test_token', $result['access_token']);
        $this->assertEquals($user, $result['user']);
    }

    public function test_login_returns_token_on_success()
    {
        $credentials = [
            'email' => 'john@example.com',
            'password' => 'password',
        ];

        $user = Mockery::mock(User::class)->makePartial();
        $user->password = 'hashed_password';
        $user->shouldReceive('createToken')->andReturn((object)['plainTextToken' => 'test_token']);

        $this->repositoryMock->shouldReceive('findByEmail')
            ->once()
            ->with($credentials['email'])
            ->andReturn($user);

        Hash::shouldReceive('check')
            ->once()
            ->with($credentials['password'], 'hashed_password')
            ->andReturn(true);

        $result = $this->authService->login($credentials);

        $this->assertEquals('test_token', $result['access_token']);
    }

    public function test_login_throws_exception_on_invalid_credentials()
    {
        $credentials = [
            'email' => 'john@example.com',
            'password' => 'wrong_password',
        ];

        $user = Mockery::mock(User::class)->makePartial();
        $user->password = 'hashed_password';

        $this->repositoryMock->shouldReceive('findByEmail')
            ->once()
            ->with($credentials['email'])
            ->andReturn($user);

        Hash::shouldReceive('check')
            ->once()
            ->andReturn(false);

        $this->expectException(ValidationException::class);
        $this->authService->login($credentials);
    }

    public function test_logout_deletes_current_token()
    {
        $tokenMock = Mockery::mock(PersonalAccessToken::class);
        $tokenMock->shouldReceive('delete')->once();

        $user = Mockery::mock(User::class)->makePartial();
        $user->shouldReceive('currentAccessToken')->once()->andReturn($tokenMock);

        $this->authService->logout($user);
        $this->assertTrue(true);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
