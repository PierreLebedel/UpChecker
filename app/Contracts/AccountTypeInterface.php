<?php

namespace App\Contracts;

use App\Models\Account;

interface AccountTypeInterface
{

    public function __construct(Account $account);

    public function getAccountName(): string;

    public function getFormView(): string;

}
