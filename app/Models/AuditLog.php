<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    protected $table = 'quiz_audit_logs';

    protected $fillable = [
        'action',
        'model_type',
        'model_id',
        'user_identifier',
        'old_values',
        'new_values',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
    ];

    const UPDATED_AT = null;

    /**
     * Get audit log for a specific model
     */
    public static function forModel(string $modelType, int $modelId)
    {
        return self::where('model_type', $modelType)
            ->where('model_id', $modelId)
            ->orderByDesc('created_at')
            ->get();
    }
}
