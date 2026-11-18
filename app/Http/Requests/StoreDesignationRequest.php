<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreDesignationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'        => ['required', 'string', 'max:150', 'unique:designations,name'],
            'code'        => ['nullable', 'string', 'max:20', 'unique:designations,code'],
            'description' => ['nullable', 'string', 'max:1000'],
            'is_active'   => ['boolean'],
        ];
    }
}
