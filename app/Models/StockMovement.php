<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockMovement extends Model
{
    use HasFactory;

    protected $fillable = [
        'warehouse_item_id',
        'type',
        'code',
        'item_name',
        'amount',
        'unit',
        'source_or_target',
        'operator',
    ];

    protected $casts = [
        'amount' => 'integer',
    ];

    public function item()
    {
        return $this->belongsTo(WarehouseItem::class, 'warehouse_item_id');
    }
}
