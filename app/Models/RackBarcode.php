<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RackBarcode extends Model
{
    use HasFactory;

    protected $fillable = [
        'barcode_code',
        'barcode_type',
        'rack_location_id',
        'rack_code',
        'zone',
        'label',
        'description',
        'status',
        'created_by',
        'last_scanned_at',
        'scan_count',
    ];

    protected $casts = [
        'last_scanned_at' => 'datetime',
        'scan_count' => 'integer',
    ];

    /**
     * Relationship to Rack Location
     */
    public function rackLocation(): BelongsTo
    {
        return $this->belongsTo(RackLocation::class, 'rack_location_id');
    }

    /**
     * Relationship to Barcode Logs
     */
    public function logs(): HasMany
    {
        return $this->hasMany(BarcodeLog::class, 'rack_barcode_id')->latest('created_at');
    }
}
