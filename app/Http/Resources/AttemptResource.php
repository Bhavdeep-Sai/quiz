<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AttemptResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'quiz_id' => $this->quiz_id,
            'user_name' => $this->user_name,
            'user_email' => $this->user_email,
            'user_identifier' => $this->user_identifier,
            'score' => $this->total_score,
            'marks' => $this->total_marks,
            'percentage' => $this->getPercentage(),
            'is_passed' => $this->is_passed,
            'performance_level' => $this->getPerformanceLevel(),
            'time_spent_seconds' => $this->time_spent_seconds,
            'status' => $this->status,
            'started_at' => $this->started_at,
            'submitted_at' => $this->submitted_at,
        ];
    }
}
