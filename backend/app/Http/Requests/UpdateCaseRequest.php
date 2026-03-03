<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCaseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->isAdmin() || $this->user()->isJudge();
    }

    public function rules(): array
    {
        return [
            'title' => 'sometimes|string|max:255',
            'description' => 'nullable|string|max:5000',
            'case_type' => 'sometimes|string|max:100',
            'urgency_level' => 'sometimes|integer|min:1|max:5',
            'complexity_level' => 'sometimes|nullable|string|max:100',
            'estimated_duration' => 'sometimes|numeric|min:0.25|max:24',
            'status' => 'sometimes|string|in:pending,scheduled,completed',
            'assigned_judge_id' => 'sometimes|nullable|exists:users,id',
        ];
    }
}
