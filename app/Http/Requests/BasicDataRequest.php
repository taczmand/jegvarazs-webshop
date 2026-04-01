<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BasicDataRequest extends FormRequest
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
            'id' => ['required', 'numeric', 'exists:basic_data,id'],
            'value' => [
                'nullable',
                function ($attribute, $value, $fail) {
                    $id = $this->input('id');
                    if (!$id) {
                        return;
                    }

                    $key = \App\Models\BasicData::query()->where('id', $id)->value('key');
                    if ($key === 'vehicle_km_required_day') {
                        if (!is_numeric($value) || (int) $value < 1 || (int) $value > 28) {
                            $fail('A kötelező havi km rögzítési nap csak 1 és 28 közötti szám lehet.');
                        }
                        return;
                    }

                    if ($value !== null && (!is_string($value) || mb_strlen($value) > 255)) {
                        $fail('Az érték mező legfeljebb 255 karakter hosszú szöveg lehet.');
                    }
                }
            ],
        ];
    }
}
