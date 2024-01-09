<?php

namespace App\Contracts\AccountType;

use App\Models\Account;
use App\Contracts\AccountTypeInterface;

abstract class AbstractAccountType implements AccountTypeInterface
{

    public $account;

    public function __construct(Account $account)
    {
        $this->account = $account;
    }

    public function getAccountName(): string
    {
        return $this->account->type->value.' account #'.$this->account->id;
    }

    public function getParam(string $paramName, mixed $default = null): mixed
    {
        if( is_array($this->account->params) && array_key_exists($paramName, $this->account->params) ){
            return $this->account->params[$paramName];
        }
        return $default;
    }
    
}