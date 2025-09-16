<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProductRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {

        return [
            'title' => ['required', 'string', 'max:255'],
            'tax_id' => ['required', 'numeric', 'exists:tax_categories,id'],
            'status' => ['required', 'string', 'in:active,inactive'],
            'category_id' => ['required', 'numeric', 'exists:categories,id'],
            'attributes' => ['nullable', 'array'],
            'attributes.*' => [
                'nullable',
                'string',
                'max:100',
                function ($attribute, $value, $fail) {
                    if (str_contains($value, '|')) {
                        $fail("Az {$attribute} mező nem tartalmazhatja a '|' karaktert.");
                    }
                },
            ],
        ];


    }
}
