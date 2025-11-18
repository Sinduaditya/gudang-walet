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
            'grades' => 'required|array|min:1',
            'grades.*.grade_company_name' => 'required|string|max:255',
            'grades.*.weight_grams' => 'required|numeric|min:0.01',
            'grades.*.quantity' => 'required|integer|min:1',
            'grades.*.notes' => 'nullable|string|max:500',
            'global_notes' => 'nullable|string|max:1000',
        ];
    }

    public function messages()
    {
        return [
            'grades.required' => 'Harus ada minimal 1 grade hasil.',
            'grades.min' => 'Harus ada minimal 1 grade hasil.',
            'grades.*.grade_company_name.required' => 'Nama grade perusahaan wajib diisi.',
            'grades.*.weight_grams.required' => 'Berat grade wajib diisi.',
            'grades.*.weight_grams.min' => 'Berat grade minimal 0.01 gram.',
            'grades.*.quantity.required' => 'Jumlah item wajib diisi.',
            'grades.*.quantity.min' => 'Jumlah item minimal 1.',
            'grades.*.notes.max' => 'Catatan grade maksimal 500 karakter.',
            'global_notes.max' => 'Catatan global maksimal 1000 karakter.',
        ];
    }

    public function attributes()
    {
        $attributes = [];
        
        if ($this->has('grades')) {
            foreach ($this->input('grades') as $index => $grade) {
                $gradeNumber = $index + 1;
                $attributes["grades.{$index}.grade_company_name"] = "Grade {$gradeNumber} - Nama Grade";
                $attributes["grades.{$index}.weight_grams"] = "Grade {$gradeNumber} - Berat";
                $attributes["grades.{$index}.quantity"] = "Grade {$gradeNumber} - Jumlah";
                $attributes["grades.{$index}.notes"] = "Grade {$gradeNumber} - Catatan";
            }
        }
        
        return $attributes;
    }
}