<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class StoreConsultationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Authorization is handled by the 'auth:sanctum' middleware on the route.
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'scheduled_at' => [
                'required',
                'date_format:Y-m-d H:i:s',
                Rule::unique('consultations')->where(function ($query) {
                    return $query->where('user_id', Auth::id());
                })
            ],
            'service_ids' => 'nullable|array',
            'service_ids.*' => 'exists:services,id',
        ];
    }

    public function messages()
    {
        return [
            'scheduled_at.unique' => 'Você já possui uma consulta agendada para este horário.',
        ];
    }
}
