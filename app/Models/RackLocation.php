<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RackLocation extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'zone',
        'type',
        'capacity_percent',
        'status',
    ];

    protected $casts = [
        'capacity_percent' => 'integer',
    ];
}
