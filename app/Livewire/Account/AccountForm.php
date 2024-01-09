<?php

namespace App\Livewire\Account;

use App\Enums\AccountTypeEnum;
use App\Models\Account;
use Livewire\Component;

class AccountForm extends Component
{

    public Account $account;
    public ?string $type;
    public array $params;
    public string $customkey;
    public ?string $partialFormView = null;

    public function mount(Account $account)
    {
        $this->account = $account;
        
        $this->type = $this->account->type->value ?? null;
        $this->params = $this->account->params ?? [];

        $this->customkey = uniqid();

        $this->updatedType();
    }

    public function updatedType()
    {
        $this->account->type = $this->type;

        $this->partialFormView = null;

        if ($this->type) {
            $enumCase = AccountTypeEnum::tryFrom($this->type);
            if ($enumCase) {
                $this->partialFormView = $enumCase->getInstance($this->account)->getFormView();
            }
        }
    }

    public function render()
    {
        return view('livewire.account.account-form');
    }
}
