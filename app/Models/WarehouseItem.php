<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WarehouseItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'category',
        'location',
        'stock',
        'unit',
        'base_unit',
        'packaging_unit',
        'conversion_factor',
        'min_stock',
        'status',
        'condition',
    ];

    protected $casts = [
        'stock' => 'integer',
        'min_stock' => 'integer',
        'conversion_factor' => 'integer',
    ];

    protected $appends = [
        'formatted_stock',
    ];

    /**
     * Get human-readable formatted multi-level stock string.
     */
    public function getFormattedStockAttribute(): string
    {
        $base = $this->base_unit ?: ($this->unit ?: 'Unit');
        $pkg = $this->packaging_unit;
        $factor = (int) ($this->conversion_factor ?: 1);

        if ($pkg && $factor > 1) {
            $packages = intdiv($this->stock, $factor);
            $remainder = $this->stock % $factor;

            if ($packages > 0 && $remainder > 0) {
                return "{$packages} {$pkg} + {$remainder} {$base}";
            } elseif ($packages > 0) {
                return "{$packages} {$pkg}";
            } else {
                return "{$remainder} {$base}";
            }
        }

        return "{$this->stock} {$base}";
    }

    /**
     * Convert given input quantity and unit into base unit amount.
     */
    public function toBaseAmount(int $amount, ?string $inputUnit = null): int
    {
        $factor = (int) ($this->conversion_factor ?: 1);
        if ($this->packaging_unit && $inputUnit === $this->packaging_unit && $factor > 1) {
            return $amount * $factor;
        }

        return $amount;
    }

    public function movements()
    {
        return $this->hasMany(StockMovement::class);
    }
}
