<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BarcodeLog extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'rack_barcode_id',
        'barcode_code',
        'rack_code',
        'action',
        'operator',
        'notes',
        'ip_address',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    /**
     * Relationship to Rack Barcode
     */
    public function rackBarcode(): BelongsTo
    {
        return $this->belongsTo(RackBarcode::class, 'rack_barcode_id');
    }

    /**
     * Record a barcode action log.
     */
    public static function record(string $action, string $barcodeCode, ?string $rackCode = null, ?string $notes = null, ?int $rackBarcodeId = null, ?string $operator = null): self
    {
        return self::create([
            'rack_barcode_id' => $rackBarcodeId,
            'barcode_code'    => $barcodeCode,
            'rack_code'       => $rackCode,
            'action'          => strtoupper($action),
            'operator'        => $operator ?: (auth()->check() ? auth()->user()->name : 'Petugas Terminal'),
            'notes'           => $notes,
            'ip_address'      => request()->ip(),
            'created_at'      => now(),
        ]);
    }
}
