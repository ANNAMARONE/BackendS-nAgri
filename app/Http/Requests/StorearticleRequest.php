<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorearticleRequest extends FormRequest
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
    public function rules()
   
    {
        return [
            
        ];
    }

    public function messages()
    {
        return [
            'date.after_or_equal' => 'La date ne peut pas être antérieure à aujourd\'hui.',
        ];
    }
}
