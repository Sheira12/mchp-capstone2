<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SacramentalRecord extends Model
{
    use HasFactory, SoftDeletes;

    const TYPES = [
        'baptism'          => 'Baptism',
        'first_communion'  => 'First Communion',
        'confirmation'     => 'Confirmation',
        'marriage'         => 'Marriage',
        'death_burial'     => 'Death/Burial',
    ];

    protected $fillable = [
        'parishioner_id',
        'spouse_parishioner_id',
        'type',
        'date_administered',
        'celebrant',
        'venue',
        'register_number',
        'page_number',
        'line_number',
        'godparents',
        'witnesses',
        'sponsors',
        'document_references',
        'notes',
        'recorded_by',
        'verified_by',
        'verified_at',
    ];

    protected $casts = [
        'date_administered'   => 'date',
        'godparents'          => 'array',
        'witnesses'           => 'array',
        'sponsors'            => 'array',
        'document_references' => 'array',
        'verified_at'         => 'datetime',
    ];

    public function parishioner()
    {
        return $this->belongsTo(Parishioner::class);
    }

    public function spouseParishioner()
    {
        return $this->belongsTo(Parishioner::class, 'spouse_parishioner_id');
    }

    public function recordedBy()
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    public function verifiedBy()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function certificate()
    {
        return $this->hasOne(Certificate::class);
    }

    public function auditLogs()
    {
        return $this->morphMany(AuditLog::class, 'auditable');
    }

    public function getTypeLabel(): string
    {
        return self::TYPES[$this->type] ?? ucfirst($this->type);
    }
}
