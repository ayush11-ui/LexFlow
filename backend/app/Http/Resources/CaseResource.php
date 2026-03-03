<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CaseResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'case_number' => $this->case_number,
            'title' => $this->title,
            'description' => $this->description,
            'case_type' => $this->case_type,
            'urgency_level' => $this->urgency_level,
            'complexity_level' => $this->complexity_level,
            'estimated_duration' => (float) $this->estimated_duration,
            'priority_score' => (float) $this->priority_score,
            'track' => $this->track,
            'status' => $this->status,
            'assigned_judge_id' => $this->assigned_judge_id,
            'assigned_judge' => new UserResource($this->whenLoaded('assignedJudge')),
            'hearings' => HearingResource::collection($this->whenLoaded('hearings')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
