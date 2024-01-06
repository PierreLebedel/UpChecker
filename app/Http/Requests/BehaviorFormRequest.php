<?php

namespace App\Http\Requests;

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
            'rules'   => ['required', 'array'],
            'actions' => ['required', 'array'],
        ];
    }
}
