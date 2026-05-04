<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AnswerResource extends JsonResource
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
            'question_id' => $this->question_id,
            'attempt_id' => $this->attempt_id,
            'user_answer' => $this->user_answer,
            'question_type' => $this->question_type,
            'score' => $this->score,
            'is_correct' => $this->is_correct,
            'feedback' => $this->feedback,
            'answered_at' => $this->answered_at,
            'time_spent_seconds' => $this->time_spent_seconds,
        ];
    }
}
