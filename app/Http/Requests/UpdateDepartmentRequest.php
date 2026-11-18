<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateDepartmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('department')->id;

        return [
            'name'        => ['required', 'string', 'max:150', Rule::unique('departments', 'name')->ignore($id)],
            'code'        => ['nullable', 'string', 'max:20', Rule::unique('departments', 'code')->ignore($id)],
            'description' => ['nullable', 'string', 'max:1000'],
            'is_active'   => ['boolean'],
        ];
    }
}
