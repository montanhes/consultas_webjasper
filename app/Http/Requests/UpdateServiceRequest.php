<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateServiceRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        $serviceId = $this->route('service');

        return [
            'name' => ['sometimes', 'required', 'string', 'max:255', Rule::unique('services')->ignore($serviceId)],
            'price' => ['sometimes', 'required', 'numeric', 'regex:/^\d+(\.\d{1,2})?$/', 'min:0'],
        ];
    }
}
