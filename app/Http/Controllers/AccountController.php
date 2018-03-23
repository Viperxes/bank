<?php

namespace App\Http\Controllers;

use App\Account;
use App\Rules\CheckTheFunds;
use App\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AccountController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return response()->json(Account::all(), 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'firstname' => 'required|string',
            'lastname' => 'required|string',
            'email' => 'required|string|email|max:255|unique:accounts',
        ]);

        $data['number'] =  str_random(26);
        $data['balance'] = 0;


        $account = Account::create($data);

        return response()->json($account, 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Account  $account
     * @return \Illuminate\Http\Response
     */
    public function show(Account $account)
    {
        return response()->json($account, 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Account  $account
     * @return \Illuminate\Http\Response
     */
    public function destroy(Account $account)
    {
        $account->delete();

        return response()->json(null, 204);
    }

    /**
     * Get account balance.
     *
     * @param  \App\Account  $account
     * @return \Illuminate\Http\Response
     */
    public function getBalance(Account $account)
    {
        return response()->json([
            'balance' => $account->balance
        ], 200);
    }

    /**
     * Deposit amount.
     *
     * @param  \App\Account  $account
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function deposit(Request $request, Account $account)
    {
        $data = $request->validate([
            'amount' => 'required|numeric|min:1',
        ]);

        DB::transaction(function() use ($account, $data) {
            $transactionId = Transaction::create([
                'title' => 'Wpłata',
                'amount' => $data['amount']
            ]);

            $account->increment('balance', $data['amount']);
            $account->transactions()->attach($transactionId, [
                'type' => Transaction::TYPES['DEPOSIT'],
                'balance_after_transaction' => $account->balance
            ]);
        });

        return response()->json(null, 204);
    }

    /**
     * Withdraw amount.
     *
     * @param  \App\Account  $account
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function withdraw(Request $request, Account $account)
    {
        $data = $request->validate([
            'amount' => ['required', 'numeric', 'min:1', new CheckTheFunds($account)],
        ]);

        DB::transaction(function() use ($account, $data) {
            $transactionId = Transaction::create([
                'title' => 'Wypłata',
                'amount' => $data['amount']
            ]);

            $account->decrement('balance', $data['amount']);
            $account->transactions()->attach($transactionId, [
                'type' => Transaction::TYPES['WITHDRAW'],
                'balance_after_transaction' => $account->balance
            ]);
        });

        return response()->json(null, 204);
    }

    /**
     * Save transfer.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function transfer(Request $request)
    {
        $data = $request->validate([
            'sender' => 'required|numeric|exists:accounts,id',
            'receiver' => 'required|numeric|exists:accounts,id',
            'title' => 'string|max:100',
            'amount' => ['required', 'numeric', 'min:1', new CheckTheFunds(Account::find($request->get('sender')))],
        ]);

        $sender = Account::find($data['sender']);
        $receiver = Account::find($data['receiver']);

        DB::transaction(function() use ($sender, $receiver, $data) {
            $transactionId = Transaction::create([
                'title' => $data['title'] ?? 'Przelew',
                'amount' => $data['amount']
            ]);

            $sender->decrement('balance', $data['amount']);
            $sender->transactions()->attach($transactionId, [
                'type' => Transaction::TYPES['OUTGOING'],
                'balance_after_transaction' => $sender->balance
            ]);

            $receiver->increment('balance', $data['amount']);
            $receiver->transactions()->attach($transactionId, [
                'type' => Transaction::TYPES['INCOMING'],
                'balance_after_transaction' => $receiver->balance
            ]);
        });

        return response()->json(null, 204);
    }

    /**
     * Get user transactions.
     *
     * @param  \App\Account  $account
     * @return \Illuminate\Http\Response
     */
    public function getTransactions(Account $account)
    {
        return response()->json($account->transactions, 200);
    }
}
