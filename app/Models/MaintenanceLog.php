<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MaintenanceLog extends Model
{
    use HasFactory, HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'ship_id',
        'tanggal_servis',
        'jenis_servis',
        'biaya',
        'status',
    ];

    protected $casts = [
        'tanggal_servis' => 'date',
    ];

    public function getBiayaAttribute($value): string
    {
        return 'Rp ' . number_format((float) ($value ?? 0), 0, ',', '.');
    }

    /**
     * Status constants.
     */
    const STATUS_PLANNED   = 'planned';
    const STATUS_ONGOING   = 'ongoing';
    const STATUS_COMPLETED = 'completed';

    /**
     * Get the ship that owns this maintenance log.
     */
    public function ship(): BelongsTo
    {
        return $this->belongsTo(Ship::class);
    }

    /**
     * Scope to filter by status.
     */
    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }
}
