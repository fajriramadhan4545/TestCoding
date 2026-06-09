<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class FilterMaintenanceLogRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'ship_id'   => ['sometimes', 'uuid', 'exists:ships,id'],
            'status'    => ['sometimes', 'in:planned,ongoing,completed'],
            'date_from' => ['sometimes', 'date', 'date_format:Y-m-d'],
            'date_to'   => [
                'sometimes',
                'date',
                'date_format:Y-m-d',
                $this->filled('date_from') ? 'after_or_equal:date_from' : 'nullable',
            ],
            'per_page'  => ['sometimes', 'integer', 'min:1', 'max:100'],
        ];
    }

    public function messages(): array
    {
        return [
            'ship_id.exists'          => 'Kapal tidak ditemukan.',
            'status.in'               => 'Status harus salah satu dari: planned, ongoing, completed.',
            'date_from.date_format'   => 'Format tanggal awal harus YYYY-MM-DD.',
            'date_to.date_format'     => 'Format tanggal akhir harus YYYY-MM-DD.',
            'date_to.after_or_equal'  => 'Tanggal akhir tidak boleh lebih awal dari tanggal awal.',
            'per_page.max'            => 'Maksimal 100 item per halaman.',
        ];
    }
}
