<?php

namespace Tests\Feature;

use App\Model\User;
use App\Service\UserService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TransactionTest extends TestCase
{
    use RefreshDatabase;

    protected $service;
    protected $request;
    protected $token;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new UserService(new User());

        $message = $this->service->create([
            'name' => 'Nome 1',
            'email' => 'email1@email.com',
            'cpf' => '12345678912',
            'balance' => '100',
            'password' => '123456',
        ]);

        /** @var User $user */
        $user = $message->getData();
        $this->artisan('passport:install');
        $this->token = $user->createToken('Simple Transactions Password Grant Client')->accessToken;

        $this->service->create([
            'name' => 'Nome 2',
            'email' => 'email2@email.com',
            'cpf' => '12345678913',
            'balance' => '100',
            'password' => '123456',
        ]);
    }

    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function testTransaction()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token . '',
            'Accept' => 'application/json'
        ])->post('/api/v1/transaction', [
            "value" => 1.00,
            "payee" => 2,
            "payee_type" => "user"
        ]);

        $response->assertStatus(200);
    }
}
