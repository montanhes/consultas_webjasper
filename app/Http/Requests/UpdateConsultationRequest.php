<?php

namespace App\Http\Requests;

use App\Models\Consultation;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class UpdateConsultationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $consultation = Consultation::find($this->route('consultation'));

        return $consultation && $consultation->user_id === Auth::id();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        $consultationId = $this->route('consultation');

        return [
            'title' => 'sometimes|required|string|max:255',
            'scheduled_at' => [
                'sometimes',
                'required',
                'date_format:Y-m-d H:i:s',
                Rule::unique('consultations')->where(function ($query) {
                    return $query->where('user_id', Auth::id());
                })->ignore($consultationId)
            ],
            'status' => 'sometimes|required|in:pending,confirmed,cancelled',
            'service_ids' => 'nullable|array',
            'service_ids.*' => 'exists:services,id',
        ];
    }

    public function messages()
    {
        return [
            'scheduled_at.unique' => 'Você já possui outra consulta agendada para este novo horário.',
        ];
    }
}
