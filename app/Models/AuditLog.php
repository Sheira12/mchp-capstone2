<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    use HasFactory;

    const UPDATED_AT = null; // Only created_at

    protected $fillable = [
        'user_id',
        'auditable_type',
        'auditable_id',
        'action',
        'old_values',
        'new_values',
        'ip_address',
        'user_agent',
        'description',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function auditable()
    {
        return $this->morphTo();
    }

    public static function record(
        string $action,
        Model $model,
        array $oldValues = [],
        array $newValues = [],
        string $description = ''
    ): self {
        return static::create([
            'user_id'        => auth()->id(),
            'auditable_type' => get_class($model),
            'auditable_id'   => $model->getKey(),
            'action'         => $action,
            'old_values'     => $oldValues,
            'new_values'     => $newValues,
            'ip_address'     => request()->ip(),
            'user_agent'     => request()->userAgent(),
            'description'    => $description,
        ]);
    }
}
