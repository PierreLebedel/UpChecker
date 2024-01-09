<?php

namespace App\Http\Requests;

use App\Enums\AccountTypeEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class UserAccountRequest extends FormRequest
{

    protected $errorBag = 'userAccount';


    public function rules(): array
    {
        return [
            'type' => [new Enum(AccountTypeEnum::class)],
            'params' => ['required', 'array'],
        ];
    }
}
