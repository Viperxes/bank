<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Account extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'email',
        'firstname',
        'lastname',
        'number',
        'balance'
    ];

    /**
     * The transactions that belong to the user.
     */
    public function transactions()
    {
        return $this->belongsToMany(Transaction::class, 'account_transactions')
            ->as('details')
            ->withPivot('type', 'balance_after_transaction');
    }
}
