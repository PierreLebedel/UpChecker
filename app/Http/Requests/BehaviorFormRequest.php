<?php

namespace App\Http\Requests;

use App\Enums\BehaviorRuleEnum;
use App\Enums\BehaviorActionEnum;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Foundation\Http\FormRequest;

class BehaviorFormRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'rules'          => ['required', 'array'],
            'rules.*.id'     => ['nullable'],
            'rules.*.type'   => [new Enum(BehaviorRuleEnum::class)],
            'rules.*.params' => ['nullable', 'array'],

            'actions'              => ['required', 'array'],
            'actions.*.id'         => ['nullable'],
            'actions.*.type'       => [new Enum(BehaviorActionEnum::class)],
            'actions.*.account_id' => ['nullable'],
            'actions.*.params'     => ['nullable', 'array'],
        ];
    }
}
