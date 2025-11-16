<?php

namespace App\Http\Requests\GradingGoods;

use Illuminate\Foundation\Http\FormRequest;

class Step2Request extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'quantity' => 'required|integer|min:1',
            'grade_company_name' => 'required|string|max:255',
            'weight_grams' => 'required|numeric|min:0.01',
            'notes' => 'nullable|string',
        ];
    }

    public function messages()
    {
        return [
            'quantity.required' => 'Kuantitas wajib diisi.',
            'quantity.integer' => 'Kuantitas harus berupa angka bulat.',
            'quantity.min' => 'Kuantitas minimal adalah 1.',
            'grade_company_name.required' => 'Nama grade company wajib diisi.',
            'grade_company_name.string' => 'Nama grade company harus berupa teks.',
            'grade_company_name.max' => 'Nama grade company maksimal 255 karakter.',
            'weight_grams.required' => 'Berat dalam gram wajib diisi.',
            'weight_grams.numeric' => 'Berat dalam gram harus berupa angka.',
            'weight_grams.min' => 'Berat dalam gram minimal adalah 0.01.',
            'notes.string' => 'Catatan harus berupa teks.',
        ];
    }
}
