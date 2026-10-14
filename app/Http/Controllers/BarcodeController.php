<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\BarcodeLog;
use App\Models\RackBarcode;
use App\Models\RackLocation;
use App\Models\WarehouseItem;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;

class BarcodeController extends Controller
{
    /**
     * Display the Barcode Management & Rack Generator page.
     */
    public function index(Request $request)
    {
        $statusFilter = $request->query('status', 'ALL');
        $rackFilter = $request->query('rack', 'ALL');
        $search = $request->query('search', '');

        // Query Barcodes
        $barcodesQuery = RackBarcode::query()->with('rackLocation');

        if ($statusFilter !== 'ALL') {
            $barcodesQuery->where('status', $statusFilter);
        }

        if ($rackFilter !== 'ALL') {
            $barcodesQuery->where('rack_code', $rackFilter);
        }

        if (!empty($search)) {
            $barcodesQuery->where(function ($q) use ($search) {
                $q->where('barcode_code', 'like', "%{$search}%")
                  ->orWhere('rack_code', 'like', "%{$search}%")
                  ->orWhere('label', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $barcodes = $barcodesQuery->latest()->get()->map(function ($bc) {
            // Find items currently stored on this rack location
            $items = WarehouseItem::where('location', 'like', "%{$bc->rack_code}%")->get()->map(function ($item) {
                return [
                    'id' => $item->id,
                    'code' => $item->code,
                    'name' => $item->name,
                    'category' => $item->category,
                    'stock' => $item->stock,
                    'unit' => $item->unit,
                    'base_unit' => $item->base_unit ?: ($item->unit ?: 'Unit'),
                    'packaging_unit' => $item->packaging_unit,
                    'conversion_factor' => (int) ($item->conversion_factor ?: 1),
                    'formatted_stock' => $item->formatted_stock,
                    'min_stock' => $item->min_stock,
                    'status' => $item->status,
                    'condition' => $item->condition,
                    'location' => $item->location,
                ];
            });

            return [
                'id' => $bc->id,
                'barcode_code' => $bc->barcode_code,
                'barcode_type' => $bc->barcode_type,
                'rack_code' => $bc->rack_code,
                'zone' => $bc->zone ?? ($bc->rackLocation?->zone ?? 'Zone A'),
                'label' => $bc->label,
                'description' => $bc->description,
                'status' => $bc->status,
                'created_by' => $bc->created_by,
                'last_scanned_at' => $bc->last_scanned_at?->toIso8601String(),
                'scan_count' => $bc->scan_count,
                'items_count' => $items->count(),
                'total_quantity' => $items->sum('stock'),
                'items' => $items,
                'created_at' => $bc->created_at?->toIso8601String(),
            ];
        });

        // Available Rack Locations from DB
        $racks = RackLocation::all()->map(function ($r) {
            $currentItemsCount = WarehouseItem::where('location', 'like', "%{$r->code}%")->count();
            return [
                'id' => $r->id,
                'code' => $r->code,
                'zone' => $r->zone,
                'type' => $r->type,
                'capacity_percent' => $r->capacity_percent,
                'status' => $r->status,
                'items_count' => $currentItemsCount,
            ];
        });

        // Barcode Activity & Scan Logs
        $logs = BarcodeLog::latest('created_at')
            ->limit(50)
            ->get()
            ->map(function ($log) {
                return [
                    'id' => $log->id,
                    'barcode_code' => $log->barcode_code,
                    'rack_code' => $log->rack_code,
                    'action' => $log->action,
                    'operator' => $log->operator,
                    'notes' => $log->notes,
                    'ip_address' => $log->ip_address,
                    'created_at' => $log->created_at?->toIso8601String(),
                ];
            });

        // Summary KPI Metrics
        $totalBarcodes = RackBarcode::count();
        $activeCount = RackBarcode::where('status', 'ACTIVE')->count();
        $inactiveCount = RackBarcode::where('status', 'INACTIVE')->count();
        $totalScans = (int) RackBarcode::sum('scan_count');

        return Inertia::render('Barcodes/Index', [
            'barcodes' => $barcodes,
            'racks' => $racks,
            'logs' => $logs,
            'allItems' => WarehouseItem::all(['id', 'code', 'name', 'location', 'category', 'stock', 'unit']),
            'metrics' => [
                'totalBarcodes' => $totalBarcodes,
                'activeCount'   => $activeCount,
                'inactiveCount' => $inactiveCount,
                'totalScans'    => $totalScans,
            ],
            'filters' => [
                'status' => $statusFilter,
                'rack'   => $rackFilter,
                'search' => $search,
            ],
        ]);
    }

    /**
     * Get live list of all distinct racks from database (RackLocation + RackBarcode + WarehouseItem).
     */
    public function racksList()
    {
        $locations = RackLocation::orderBy('code')->get();
        $barcodes = RackBarcode::where('status', 'ACTIVE')->get();

        $racksMap = [];

        foreach ($locations as $loc) {
            $itemsCount = WarehouseItem::where('location', 'like', "%{$loc->code}%")->count();
            $racksMap[$loc->code] = [
                'id' => $loc->id,
                'code' => $loc->code,
                'zone' => $loc->zone ?: 'Zone A',
                'type' => $loc->type ?: 'Standard Rack',
                'barcode_code' => null,
                'label' => 'Rak ' . $loc->code,
                'items_count' => $itemsCount,
            ];
        }

        foreach ($barcodes as $bc) {
            $itemsCount = WarehouseItem::where('location', 'like', "%{$bc->rack_code}%")->count();
            if (!isset($racksMap[$bc->rack_code])) {
                $racksMap[$bc->rack_code] = [
                    'id' => $bc->id,
                    'code' => $bc->rack_code,
                    'zone' => $bc->zone ?: 'Zone A',
                    'type' => 'Standard Rack',
                    'barcode_code' => $bc->barcode_code,
                    'label' => $bc->label,
                    'items_count' => $itemsCount,
                ];
            } else {
                $racksMap[$bc->rack_code]['barcode_code'] = $bc->barcode_code;
                $racksMap[$bc->rack_code]['label'] = $bc->label;
            }
        }

        // Also check if any item exists in a location that might not have a formal rack record
        $itemLocations = WarehouseItem::pluck('location')->unique();
        foreach ($itemLocations as $locString) {
            if (preg_match('/\b([A-Z]-\d{2})\b/i', $locString, $m)) {
                $code = strtoupper($m[1]);
                if (!isset($racksMap[$code])) {
                    $racksMap[$code] = [
                        'id' => null,
                        'code' => $code,
                        'zone' => 'Zone A',
                        'type' => 'Standard Rack',
                        'barcode_code' => null,
                        'label' => 'Rak ' . $code,
                        'items_count' => WarehouseItem::where('location', 'like', "%{$code}%")->count(),
                    ];
                }
            }
        }

        ksort($racksMap);

        return response()->json(array_values($racksMap));
    }

    /**
     * Store a newly generated barcode for a rack.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'rack_code'    => 'required|string|max:50',
            'zone'         => 'nullable|string|max:50',
            'label'        => 'required|string|max:150',
            'barcode_type' => 'required|in:CODE128,QR_CODE',
            'custom_code'  => 'nullable|string|max:50|unique:rack_barcodes,barcode_code',
            'description'  => 'nullable|string|max:500',
        ]);

        $rackCode = trim($validated['rack_code']);
        $matchedRack = RackLocation::where('code', $rackCode)->first();
        if (!$matchedRack) {
            $matchedRack = RackLocation::create([
                'code' => $rackCode,
                'zone' => $validated['zone'] ?: 'Zone A',
                'type' => 'Standard Rack',
                'capacity_percent' => 0,
                'status' => 'Available',
            ]);
        }
        $zone = $validated['zone'] ?: ($matchedRack->zone ?: 'Zone A');

        // Determine Barcode ID: custom or generated
        if (!empty($validated['custom_code'])) {
            $barcodeCode = strtoupper(trim($validated['custom_code']));
        } else {
            $prefix = $validated['barcode_type'] === 'QR_CODE' ? 'QR' : 'BC';
            $sanitizedRack = preg_replace('/[^A-Za-z0-9]/', '', $rackCode);
            $randomDigits = str_pad(mt_rand(100, 9999), 4, '0', STR_PAD_LEFT);
            $barcodeCode = "{$prefix}-{$sanitizedRack}-{$randomDigits}";

            // Ensure uniqueness
            while (RackBarcode::where('barcode_code', $barcodeCode)->exists()) {
                $randomDigits = str_pad(mt_rand(100, 9999), 4, '0', STR_PAD_LEFT);
                $barcodeCode = "{$prefix}-{$sanitizedRack}-{$randomDigits}";
            }
        }

        $operator = auth()->check() ? auth()->user()->name : 'Petugas Terminal';

        $barcode = RackBarcode::create([
            'barcode_code'     => $barcodeCode,
            'barcode_type'     => $validated['barcode_type'],
            'rack_location_id' => $matchedRack?->id,
            'rack_code'        => $rackCode,
            'zone'             => $zone,
            'label'            => $validated['label'],
            'description'      => $validated['description'] ?? null,
            'status'           => 'ACTIVE',
            'created_by'       => $operator,
            'scan_count'       => 0,
        ]);

        // 1. Barcode Audit Log
        BarcodeLog::record(
            'GENERATE',
            $barcode->barcode_code,
            $barcode->rack_code,
            "Barcode rak didaftarkan: {$barcode->label} ({$barcode->barcode_type})",
            $barcode->id,
            $operator
        );

        // 2. Global System Activity Log
        ActivityLog::record(
            'MAINTENANCE',
            "Barcode Rak Didaftarkan: {$barcode->barcode_code}",
            "Untuk Lokasi Rak [{$barcode->rack_code} - {$zone}] | Label: {$barcode->label}",
            $operator,
            'blue'
        );

        return redirect()->back()->with('success', "Barcode {$barcode->barcode_code} untuk Rak {$rackCode} berhasil digenerate!");
    }

    /**
     * Toggle status between ACTIVE and INACTIVE.
     */
    public function toggleStatus(RackBarcode $barcode)
    {
        $oldStatus = $barcode->status;
        $newStatus = $oldStatus === 'ACTIVE' ? 'INACTIVE' : 'ACTIVE';
        $barcode->status = $newStatus;
        $barcode->save();

        $operator = auth()->check() ? auth()->user()->name : 'Petugas Terminal';

        BarcodeLog::record(
            'STATUS_CHANGE',
            $barcode->barcode_code,
            $barcode->rack_code,
            "Status barcode diubah dari {$oldStatus} ke {$newStatus}",
            $barcode->id,
            $operator
        );

        return redirect()->back()->with('success', "Status barcode {$barcode->barcode_code} diubah menjadi {$newStatus}.");
    }

    /**
     * Delete a barcode record.
     */
    public function destroy(RackBarcode $barcode)
    {
        $code = $barcode->barcode_code;
        $rack = $barcode->rack_code;
        $operator = auth()->check() ? auth()->user()->name : 'Petugas Terminal';

        BarcodeLog::record(
            'DELETE',
            $code,
            $rack,
            "Barcode rak {$code} dihapus dari master sistem",
            null,
            $operator
        );

        $barcode->delete();

        return redirect()->back()->with('success', "Barcode {$code} berhasil dihapus.");
    }

    /**
     * Log a print event when user prints or exports a barcode sticker.
     */
    public function logPrint(RackBarcode $barcode)
    {
        $operator = auth()->check() ? auth()->user()->name : 'Petugas Terminal';

        BarcodeLog::record(
            'PRINT',
            $barcode->barcode_code,
            $barcode->rack_code,
            "Stiker barcode dicetak / diekspor oleh {$operator}",
            $barcode->id,
            $operator
        );

        return response()->json(['success' => true]);
    }

    /**
     * Scan or simulate scanning a barcode.
     */
    public function scan(Request $request)
    {
        $request->validate([
            'code' => 'required|string',
        ]);

        $code = trim($request->code);

        // If input contains full URL with scan parameter, extract it
        if (preg_match('/[?&]scan=([^&]+)/i', $code, $matches)) {
            $code = urldecode($matches[1]);
        }

        // Find by barcode_code or rack_code
        $barcode = RackBarcode::where('barcode_code', $code)
            ->orWhere('rack_code', $code)
            ->first();

        // Also fallback to cleaned rack code (e.g. A01 matching A-01)
        if (!$barcode) {
            $cleanCode = str_replace(['-', ' ', '_'], '', strtoupper($code));
            $barcode = RackBarcode::all()->first(function ($b) use ($cleanCode) {
                return str_replace(['-', ' ', '_'], '', strtoupper($b->rack_code)) === $cleanCode
                    || str_replace(['-', ' ', '_'], '', strtoupper($b->barcode_code)) === $cleanCode;
            });
        }

        $operator = auth()->check() ? auth()->user()->name : 'Petugas Terminal';

        // If barcode not found yet, check if this is an existing rack location in the system
        if (!$barcode) {
            $matchedLocation = RackLocation::where('code', $code)->first();
            if (!$matchedLocation) {
                $cleanCode = str_replace(['-', ' ', '_'], '', strtoupper($code));
                $matchedLocation = RackLocation::all()->first(function ($r) use ($cleanCode) {
                    return str_replace(['-', ' ', '_'], '', strtoupper($r->code)) === $cleanCode;
                });
            }

            if ($matchedLocation) {
                // Auto-create active barcode entry for this master rack
                $cleanRack = preg_replace('/[^A-Za-z0-9]/', '', $matchedLocation->code);
                $randomDigits = str_pad(mt_rand(100, 9999), 4, '0', STR_PAD_LEFT);
                $barcode = RackBarcode::create([
                    'barcode_code'     => "BC-{$cleanRack}-{$randomDigits}",
                    'barcode_type'     => 'CODE128',
                    'rack_location_id' => $matchedLocation->id,
                    'rack_code'        => $matchedLocation->code,
                    'zone'             => $matchedLocation->zone ?: 'Zone A',
                    'label'            => 'Rak ' . $matchedLocation->code . ' ' . ($matchedLocation->zone ?: 'Zone A'),
                    'description'      => 'Registrasi otomatis barcode saat pemindaian rak master',
                    'status'           => 'ACTIVE',
                    'created_by'       => $operator,
                    'scan_count'       => 0,
                ]);
            }
        }

        if (!$barcode) {
            BarcodeLog::record('SCAN_FAILED', $code, null, 'Kode barcode atau rak tidak ditemukan / belum terdaftar', null, $operator);

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => "Barcode atau kode rak '{$code}' tidak ditemukan di sistem.",
                ], 200);
            }

            return redirect()->back()->withErrors(['scan' => "Barcode '{$code}' tidak ditemukan di master rak."]);
        }

        // Increment scan count and update timestamp
        $barcode->increment('scan_count');
        $barcode->update(['last_scanned_at' => now()]);

        // Find items currently stored on this rack
        $items = WarehouseItem::where('location', 'like', "%{$barcode->rack_code}%")->get();

        BarcodeLog::record(
            'SCAN',
            $barcode->barcode_code,
            $barcode->rack_code,
            "Pemindaian sukses pada Rak [{$barcode->rack_code}] - Ditemukan {$items->count()} item aset",
            $barcode->id,
            $operator
        );

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'barcode' => $barcode,
                'items' => $items,
            ]);
        }

        return redirect()->back()->with('scanResult', [
            'barcode' => $barcode,
            'items' => $items,
        ]);
    }
}
