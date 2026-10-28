<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\BarcodeLog;
use App\Models\RackBarcode;
use App\Models\RackLocation;
use App\Models\WarehouseItem;
use Illuminate\Http\Request;
use Inertia\Inertia;

class RackController extends Controller
{
    /**
     * Display the dedicated rack management & CRUD page.
     * Can be accessed via rack_code (e.g. A-01) or barcode_code (e.g. BC-A01-1024).
     */
    public function show(Request $request, string $code)
    {
        $rawCode = trim($code);

        // 1. Resolve RackBarcode by barcode_code or rack_code
        $barcode = RackBarcode::where('rack_code', $rawCode)
            ->orWhere('barcode_code', $rawCode)
            ->first();

        // Fallback: match without hyphens/spaces
        if (!$barcode) {
            $cleanCode = str_replace(['-', ' ', '_'], '', strtoupper($rawCode));
            $barcode = RackBarcode::all()->first(function ($b) use ($cleanCode) {
                return str_replace(['-', ' ', '_'], '', strtoupper($b->rack_code)) === $cleanCode
                    || str_replace(['-', ' ', '_'], '', strtoupper($b->barcode_code)) === $cleanCode;
            });
        }

        // 2. If no RackBarcode exists yet, find or create from RackLocation
        if (!$barcode) {
            $matchedLocation = RackLocation::where('code', $rawCode)->first();
            if (!$matchedLocation) {
                $cleanCode = str_replace(['-', ' ', '_'], '', strtoupper($rawCode));
                $matchedLocation = RackLocation::all()->first(function ($r) use ($cleanCode) {
                    return str_replace(['-', ' ', '_'], '', strtoupper($r->code)) === $cleanCode;
                });
            }

            if ($matchedLocation) {
                $cleanRack = preg_replace('/[^A-Za-z0-9]/', '', $matchedLocation->code);
                $randomDigits = str_pad(mt_rand(100, 9999), 4, '0', STR_PAD_LEFT);
                $operator = auth()->check() ? auth()->user()->name : 'Petugas Terminal';

                $barcode = RackBarcode::create([
                    'barcode_code'     => "BC-{$cleanRack}-{$randomDigits}",
                    'barcode_type'     => 'CODE128',
                    'rack_location_id' => $matchedLocation->id,
                    'rack_code'        => $matchedLocation->code,
                    'zone'             => $matchedLocation->zone ?: 'Zone A',
                    'label'            => 'Rak ' . $matchedLocation->code,
                    'description'      => 'Registrasi otomatis barcode untuk rak ' . $matchedLocation->code,
                    'status'           => 'ACTIVE',
                    'created_by'       => $operator,
                    'scan_count'       => 0,
                ]);
            }
        }

        // 3. If still not found, create on the fly as a valid new rack
        if (!$barcode) {
            $cleanRack = preg_replace('/[^A-Za-z0-9]/', '', strtoupper($rawCode));
            $randomDigits = str_pad(mt_rand(100, 9999), 4, '0', STR_PAD_LEFT);
            $operator = auth()->check() ? auth()->user()->name : 'Petugas Terminal';

            $newLocation = RackLocation::firstOrCreate(
                ['code' => strtoupper($rawCode)],
                [
                    'zone' => 'Zone A',
                    'type' => 'Standard Rack',
                    'capacity_percent' => 0,
                    'status' => 'Available',
                ]
            );

            $barcode = RackBarcode::create([
                'barcode_code'     => "BC-{$cleanRack}-{$randomDigits}",
                'barcode_type'     => 'CODE128',
                'rack_location_id' => $newLocation->id,
                'rack_code'        => strtoupper($rawCode),
                'zone'             => $newLocation->zone ?: 'Zone A',
                'label'            => 'Rak ' . strtoupper($rawCode),
                'description'      => 'Registrasi otomatis saat pemindaian pertama',
                'status'           => 'ACTIVE',
                'created_by'       => $operator,
                'scan_count'       => 0,
            ]);
        }

        // Increment scan count and update timestamp
        $barcode->increment('scan_count');
        $barcode->update(['last_scanned_at' => now()]);

        $operator = auth()->check() ? auth()->user()->name : 'Petugas Terminal';
        BarcodeLog::record(
            'SCAN',
            $barcode->barcode_code,
            $barcode->rack_code,
            "Buka Halaman Kelola Rak [{$barcode->rack_code}]: {$barcode->label}",
            $barcode->id,
            $operator
        );

        // 4. Query Items stored in this rack
        $items = WarehouseItem::where('location', 'like', "%{$barcode->rack_code}%")
            ->latest('updated_at')
            ->get()
            ->map(function ($item) {
                return [
                    'id'                => $item->id,
                    'code'              => $item->code,
                    'name'              => $item->name,
                    'category'          => $item->category,
                    'location'          => $item->location,
                    'stock'             => $item->stock,
                    'unit'              => $item->unit,
                    'base_unit'         => $item->base_unit ?: ($item->unit ?: 'Unit'),
                    'packaging_unit'    => $item->packaging_unit,
                    'conversion_factor' => (int) ($item->conversion_factor ?: 1),
                    'formatted_stock'   => $item->formatted_stock,
                    'min_stock'         => $item->min_stock,
                    'status'            => $item->status,
                    'condition'         => $item->condition,
                    'created_at'        => $item->created_at?->toIso8601String(),
                    'updated_at'        => $item->updated_at?->toIso8601String(),
                ];
            });

        // 5. Distinct categories for autocomplete / filter
        $categories = WarehouseItem::pluck('category')
            ->filter()
            ->unique()
            ->values()
            ->toArray();

        // 6. Available rack locations list for item relocation
        $availableLocations = RackLocation::pluck('code')->all();

        return Inertia::render('Racks/Show', [
            'rack' => [
                'id'              => $barcode->id,
                'barcode_code'    => $barcode->barcode_code,
                'barcode_type'    => $barcode->barcode_type,
                'rack_code'       => $barcode->rack_code,
                'zone'            => $barcode->zone ?? 'Zone A',
                'label'           => $barcode->label,
                'description'     => $barcode->description,
                'status'          => $barcode->status,
                'created_by'      => $barcode->created_by,
                'scan_count'      => $barcode->scan_count,
                'last_scanned_at' => $barcode->last_scanned_at?->toIso8601String(),
                'created_at'      => $barcode->created_at?->toIso8601String(),
            ],
            'items'              => $items,
            'categories'         => $categories,
            'availableLocations' => $availableLocations,
        ]);
    }

    /**
     * Update rack details (Nama / Label Rak, Zone, Description).
     */
    public function update(Request $request, string $code)
    {
        $barcode = RackBarcode::where('rack_code', $code)
            ->orWhere('barcode_code', $code)
            ->firstOrFail();

        $validated = $request->validate([
            'label'       => 'required|string|max:150',
            'zone'        => 'nullable|string|max:50',
            'description' => 'nullable|string|max:500',
            'status'      => 'nullable|in:ACTIVE,INACTIVE',
        ]);

        $oldLabel = $barcode->label;
        $oldZone = $barcode->zone;

        $barcode->update([
            'label'       => $validated['label'],
            'zone'        => $validated['zone'] ?? $barcode->zone,
            'description' => $validated['description'] ?? null,
            'status'      => $validated['status'] ?? $barcode->status,
        ]);

        // Also update matching RackLocation if present
        if ($barcode->rackLocation) {
            $barcode->rackLocation->update([
                'zone' => $validated['zone'] ?? $barcode->rackLocation->zone,
            ]);
        }

        $operator = auth()->check() ? auth()->user()->name : 'Petugas Terminal';

        BarcodeLog::record(
            'UPDATE',
            $barcode->barcode_code,
            $barcode->rack_code,
            "Perubahan Informasi Rak: '{$oldLabel}' -> '{$barcode->label}' (Zona: {$barcode->zone})",
            $barcode->id,
            $operator
        );

        ActivityLog::record(
            'MAINTENANCE',
            "Informasi Rak Diperbarui: {$barcode->rack_code}",
            "Nama Rak diubah menjadi [{$barcode->label}] | Zona: {$barcode->zone}",
            $operator,
            'blue'
        );

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Informasi Rak {$barcode->rack_code} berhasil diperbarui.",
                'rack'    => $barcode,
            ]);
        }

        return redirect()->back()->with('success', "Nama dan informasi Rak {$barcode->rack_code} berhasil diperbarui.");
    }
}
