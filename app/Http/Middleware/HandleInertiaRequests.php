<?php

namespace App\Http\Middleware;

use App\Models\RackLocation;
use App\Models\WarehouseItem;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        // Hitung okupansi rak aktual global dari database
        $actualRackCapacity = 0;
        if (\Illuminate\Support\Facades\Schema::hasTable('warehouse_items') && \Illuminate\Support\Facades\Schema::hasTable('rack_locations')) {
            $items = WarehouseItem::all();
            $racks = RackLocation::all();

            if ($racks->count() > 0) {
                $totalPercent = $racks->map(function ($rack) use ($items) {
                    $stockCount = $items->filter(function ($item) use ($rack) {
                        return str_contains(strtoupper($item->location), strtoupper($rack->code));
                    })->sum('stock');
                    return $stockCount > 0 ? min(100, round(($stockCount / 20) * 100)) : 0;
                })->avg();
                $actualRackCapacity = round($totalPercent);
            }
        }

        return [
            ...parent::share($request),
            'auth' => [
                'user' => $request->user(),
            ],
            'facility' => [
                'name' => 'WH-KRWNG Barat',
                'status' => 'Online',
                'actualRackCapacity' => $actualRackCapacity,
            ],
        ];
    }
}
