<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UnitRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->input('id');

        return [
            'name' => ['required', 'string', 'max:255', Rule::unique('units', 'name')->ignore($id)],
            'abbreviation' => ['required', 'string', 'max:32', Rule::unique('units', 'abbreviation')->ignore($id)],
            'active' => ['nullable', 'boolean'],
        ];
    }
}
