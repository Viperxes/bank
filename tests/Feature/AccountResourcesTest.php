<?php

namespace Tests\Feature;

use App\Account;
use App\Transaction;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AccountResourcesTest extends TestCase
{
    public function testGetAccounts()
    {
        factory(Account::class, 12)->create();

        $this->json('GET', 'api/accounts')
            ->assertStatus(200)
            ->assertJsonStructure([
                [
                    'id',
                    'email',
                    'firstname',
                    'lastname',
                    'number',
                    'balance',
                    'created_at',
                    'updated_at',
                ]
            ]);
    }

    public function testStoreAccount()
    {
        $payload = [
            'firstname' => 'Test',
            'lastname' => 'Best',
            'email' => 'test@test.com'
        ];

        $this->json('POST', 'api/accounts', $payload)
            ->assertStatus(201)
            ->assertJsonStructure([
                'id',
                'email',
                'firstname',
                'lastname',
                'number',
                'balance',
                'created_at',
                'updated_at',
            ]);
    }

    public function testGetAccount()
    {
        $account = factory(Account::class)->create();

        $this->json('GET', 'api/accounts/' . $account->id)
            ->assertStatus(200)
            ->assertJsonStructure([
                'id',
                'email',
                'firstname',
                'lastname',
                'number',
                'balance',
                'created_at',
                'updated_at',
            ]);
    }

    public function testDeleteAccount()
    {
        $account = factory(Account::class)->create();

        $this->json('DELETE', 'api/accounts/' . $account->id)
            ->assertStatus(204);
    }

    public function testGetBalanceAccount()
    {
        $account = factory(Account::class)->create();

        $this->json('GET', 'api/accounts/' . $account->id . '/balance')
            ->assertStatus(200)
            ->assertJsonStructure([
                'balance'
            ]);
    }

    public function testAccountDeposit()
    {
        $account = factory(Account::class)->create();
        $payload = [
            'amount' => 100
        ];

        $this->json('PUT', 'api/accounts/' . $account->id . '/deposit', $payload)
            ->assertStatus(204);
    }

    public function testAccountWithdraw()
    {
        $account = factory(Account::class)->create([
            'balance' => 1000
        ]);
        $payload = [
            'amount' => 50
        ];

        $this->json('PUT', 'api/accounts/' . $account->id . '/withdraw', $payload)
            ->assertStatus(204);
    }

    public function testTransfer()
    {
        $sender = factory(Account::class)->create([
            'balance' => 500
        ]);
        $receiver = factory(Account::class)->create();

        $payload = [
            'sender' => $sender->id,
            'receiver' => $receiver->id,
            'title' => 'Test transfer',
            'amount' => 150
        ];

        $this->json('POST', 'api/accounts/transfer', $payload)
            ->assertStatus(204);
    }

    public function testGetTransactions()
    {
        $sender = factory(Account::class)->create(['balance' => 500]);
        $receiver = factory(Account::class)->create();

        factory(Transaction::class)->create(['amount' => 50])
            ->each(function ($transaction) use ($sender, $receiver) {
            $transaction->accounts()->attach($sender->id, [
                'type' => Transaction::TYPES['OUTGOING'],
                'balance_after_transaction' => $sender->balance - $transaction->amount
            ]);
            $transaction->accounts()->attach($receiver->id, [
                'type' => Transaction::TYPES['INCOMING'],
                'balance_after_transaction' => $sender->balance + $transaction->amount
            ]);
        });

        factory(Transaction::class)->create(['amount' => 4300])->each(function ($transaction) use ($sender) {
            $transaction->accounts()->attach($sender->id, [
                'type' => Transaction::TYPES['DEPOSIT'],
                'balance_after_transaction' => $sender->balance + $transaction->amount
            ]);
        });

        factory(Transaction::class)->create(['amount' => 300])->each(function ($transaction) use ($sender) {
            $transaction->accounts()->attach($sender->id, [
                'type' => Transaction::TYPES['WITHDRAW'],
                'balance_after_transaction' => $sender->balance - $transaction->amount
            ]);
        });

        $this->json('GET', 'api/accounts/' . $sender->id . '/transactions')
            ->assertStatus(200)
            ->assertJsonStructure([
                [
                    'id',
                    'title',
                    'amount',
                    'created_at',
                    'updated_at',
                    'details' => [
                        'account_id',
                        'transaction_id',
                        'type',
                        'balance_after_transaction',
                    ]
                ]
            ]);
    }
}
