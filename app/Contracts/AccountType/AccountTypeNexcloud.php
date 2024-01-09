<?php

namespace App\Contracts\AccountType;

use Illuminate\Support\Str;
use App\Contracts\AccountType\AbstractAccountType;

class AccountTypeNexcloud extends AbstractAccountType
{

    public function getAccountName(): string
    {
        $username = $this->getParam('username');
        $domain = $this->getParam('domain');

        if( $username && $domain ){
            return $username.'@'.$this->getCleanDomain($domain);
        }

        return 'Nexcloud account #'.$this->account->id;
    }

    public function getCleanDomain(string $domain): string
    {
        return Str::of($domain)
            ->replaceStart('https://', '')
            ->replaceStart('http://', '')
            ->replaceEnd('/', '');
    }

    public function getFormView(): string
    {
        return 'profile.account.nextcloud';
    }
    
}