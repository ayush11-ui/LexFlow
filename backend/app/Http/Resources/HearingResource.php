<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class HearingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'case_id' => $this->case_id,
            'judge_id' => $this->judge_id,
            'courtroom' => $this->courtroom,
            'hearing_date' => $this->hearing_date,
            'start_time' => $this->start_time,
            'end_time' => $this->end_time,
            'status' => $this->status,
            'court_case' => new CaseResource($this->whenLoaded('courtCase')),
            'judge' => new UserResource($this->whenLoaded('judge')),
        ];
    }
}
