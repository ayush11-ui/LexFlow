<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class JudgeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'specialization' => $this->specialization,
            'assigned_cases_count' => $this->assigned_cases_count ?? 0,
            'utilization' => $this->utilization ?? 0,
            'availability' => $this->whenLoaded('availability'),
        ];
    }
}
