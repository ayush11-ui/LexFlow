<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCaseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->isAdmin() || $this->user()->isClerk();
    }

    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:5000',
            'case_type' => 'required|string|max:100',
            'urgency_level' => 'required|integer|min:1|max:5',
            'complexity_level' => 'nullable|string|max:100',
            'estimated_duration' => 'required|numeric|min:0.25|max:24',
        ];
    }

    public function messages(): array
    {
        return [
            'urgency_level.min' => 'Urgency level must be between 1 and 5.',
            'urgency_level.max' => 'Urgency level must be between 1 and 5.',
            'estimated_duration.min' => 'Estimated duration must be at least 15 minutes.',
        ];
    }
}
