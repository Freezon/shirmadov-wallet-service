<?php

namespace Tests\Unit;

use App\Enums\TransactionType;
use App\Models\Transaction;
use App\Models\User;
use App\Services\CurrencyService;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TransactionControllerTest extends TestCase
{

    use RefreshDatabase;

    protected $user;
    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create(['balance'=>50.11]);
        $this->actingAs($this->user,'sanctum');
    }

    public function test_create_credit_transaction()
    {
        $requestData = [
            'type' => TransactionType::CREDIT->value,
            'amount' => 100.11,
            'target_user_id' => null
        ];

        $res = $this->postJson('api/balance',$requestData);

        $res->assertStatus(200)
        ->assertJson([
            'status'=>true,
            'message'=>'Operation completed successfully',
        ]);

        $this->assertDatabaseHas('transactions',[
           'user_id'=>$this->user->id,
           'amount'=>100.11,
           'type'=>TransactionType::CREDIT->value,
        ]);

        $this->assertEquals(150.22,$this->user->refresh()->balance);
    }

    public function test_create_debit_transaction()
    {
        $requestData = [
            'type' => TransactionType::DEBIT->value,
            'amount' => 10.11,
            'target_user_id' => null
        ];

        $res = $this->postJson('api/balance',$requestData);

        $res->assertStatus(200)
            ->assertJson([
                'status'=>true,
                'message'=>'Operation completed successfully',
            ]);

        $this->assertDatabaseHas('transactions',[
            'user_id'=>$this->user->id,
            'amount'=>10.11,
            'type'=>TransactionType::DEBIT->value,
        ]);

        $this->assertEquals(40.00,$this->user->refresh()->balance);
    }

    public function test_create_transfer_transaction()
    {
        $targetUser = User::factory()->create();

        $requestData = [
            'type' => TransactionType::TRANSFER->value,
            'amount' => 20.33,
            'target_user_id' => $targetUser->id
        ];

        $res = $this->postJson('api/balance',$requestData);

        $res->assertStatus(200)
            ->assertJson([
                'status'=>true,
                'message'=>'Operation completed successfully',
            ]);

        $this->assertDatabaseHas('transactions',[
            'user_id'=>$this->user->id,
            'amount'=>20.33,
            'type'=>TransactionType::TRANSFER->value,
            'target_user_id'=>$targetUser->id,
        ]);

        $this->assertEquals(29.78,$this->user->refresh()->balance);
    }

    public function test_invalid_transaction_transfer()
    {
        $requestData = [
            'type' => 'invalid_type',
            'amount' => 20.33,
            'target_user_id' => null
        ];

        $res = $this->postJson('api/balance',$requestData);

        $res->assertStatus(422);
    }

    public function test_show_balance_with_valid_currency()
    {
        $mockCurrencyService = $this->createMock(CurrencyService::class);

        $mockCurrencyService->method('convert')
            ->willReturn([
                'data' => [
                    'EUR' => 0.013,
                    'USD' => 0.011
                ]
            ]);

        $this->app->instance(CurrencyService::class, $mockCurrencyService);

        $requestData = ['currency' => 'USD'];

        $res = $this->getJson('api/balance', $requestData);

        $res->assertStatus(200)
            ->assertJson([
                "status" => true,
                "message" => "Successfully",
                "data" => [
                    "amount" => "50.11 RUB"
                ]
            ]);
    }

    public function test_show_balance_without_currency()
    {
        $res = $this->getJson('api/balance');

        $res->assertStatus(200)
            ->assertJson([
                "status" => true,
                "message" => "Successfully",
                "data" => [
                    "amount" => "50.11 RUB"
                ]
            ]);
    }

    public function test_show_transaction()
    {
        $user = User::factory()->create();
        $transactions = Transaction::factory()->count(3)->create([
           'user_id'=>$user->id
        ]);

        $this->actingAs($user, 'sanctum');

        $res = $this->getJson('api/transactions');

        $res->assertStatus(200);
    }


}
