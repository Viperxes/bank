<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    const TYPES = [
        'DEPOSIT' => 'deposit',
        'WITHDRAW' => 'withdraw',
        'INCOMING' => 'incoming',
        'OUTGOING' => 'outgoing'
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'title',
        'amount'
    ];

    /**
     * The users that belong to the transactions.
     */
    public function accounts()
    {
        return $this->belongsToMany(Account::class, 'account_transactions');
    }
}
