<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Ship extends Model
{
    use HasFactory, HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'nama',
        'kode_kapal',
        'tahun_pembuatan',
    ];

    protected $casts = [
        'tahun_pembuatan' => 'integer',
    ];

    public function getTotalBiayaServisAttribute($value): string
    {
        return 'Rp ' . number_format((float) ($value ?? 0), 0, ',', '.');
    }

    /**
     * Get all maintenance logs for this ship.
     */
    public function maintenanceLogs(): HasMany
    {
        return $this->hasMany(MaintenanceLog::class);
    }

    /**
     * Get the latest maintenance log for this ship.
     */
    public function latestMaintenanceLog(): HasMany
    {
        return $this->hasMany(MaintenanceLog::class)->latestOfMany('tanggal_servis');
    }
}
