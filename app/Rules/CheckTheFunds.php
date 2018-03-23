<?php

namespace App\Rules;

use App\Account;
use Illuminate\Contracts\Validation\Rule;

class CheckTheFunds implements Rule
{
    private $account;

    /**
     * Create a new rule instance.
     *
     * @param  \App\Account  $account
     * @return void
     */
    public function __construct(Account $account)
    {
        $this->account = $account;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        return $this->account->balance >= $value;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'You do not have sufficient funds on your account.';
    }
}
