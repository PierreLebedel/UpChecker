<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EndpointFormRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'url'                  => ['required', 'url'],
            'expected_status_code' => ['required'],
            'timeout'              => ['required', 'integer'],
            'follow_redirects'     => ['required', 'boolean'],
            'checkup_delay'        => ['required', 'integer'],
        ];
    }
}
