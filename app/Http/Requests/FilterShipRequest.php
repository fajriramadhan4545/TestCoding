<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class FilterShipRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'search'    => ['sometimes', 'string', 'max:100'],
            'min_biaya' => ['sometimes', 'numeric', 'min:0'],
            'max_biaya' => [
                'sometimes',
                'numeric',
                'min:0',
                $this->filled('min_biaya') ? 'gte:min_biaya' : 'nullable',
            ],
            'status'    => ['sometimes', 'in:planned,ongoing,completed'],
            'per_page'  => ['sometimes', 'integer', 'min:1', 'max:100'],
        ];
    }

    public function messages(): array
    {
        return [
            'max_biaya.gte'  => 'Biaya maksimal tidak boleh lebih kecil dari biaya minimal.',
            'status.in'      => 'Status harus salah satu dari: planned, ongoing, completed.',
            'per_page.max'   => 'Maksimal 100 item per halaman.',
        ];
    }
}
