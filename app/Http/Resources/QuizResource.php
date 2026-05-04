<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class QuizResource extends JsonResource
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
            'title' => $this->title,
            'description' => $this->description,
            'pass_percentage' => $this->pass_percentage,
            'is_published' => $this->is_published,
            'question_count' => $this->questions_count ?? $this->questions()->count(),
            'total_marks' => $this->questions()->sum('marks'),
            'attempt_count' => $this->attempts_count ?? $this->attempts()->count(),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
