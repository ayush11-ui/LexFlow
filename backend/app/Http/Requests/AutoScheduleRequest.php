<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AutoScheduleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    public function rules(): array
    {
        return [
            'case_id' => ['nullable', 'exists:cases,id'],
        ];
    }
}
