<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'user_name',
        'title',
        'description',
        'ip_address',
        'badge_color',
    ];

    /**
     * Helper to log an activity easily from anywhere in the app.
     */
    public static function record(string $type, string $title, ?string $description = null, ?string $userName = null, ?string $badgeColor = null): self
    {
        $color = $badgeColor ?: match ($type) {
            'INBOUND' => 'emerald',
            'OUTBOUND' => 'amber',
            'LOGIN' => 'blue',
            'LOGOUT' => 'slate',
            'MAINTENANCE' => 'rose',
            default => 'purple',
        };

        return self::create([
            'type' => $type,
            'user_name' => $userName ?: (auth()->check() ? auth()->user()->name : 'System'),
            'title' => $title,
            'description' => $description,
            'ip_address' => request()->ip(),
            'badge_color' => $color,
        ]);
    }
}
