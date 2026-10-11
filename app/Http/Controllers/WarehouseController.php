<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\RackLocation;
use App\Models\StockMovement;
use App\Models\WarehouseItem;
use Illuminate\Http\Request;
use Inertia\Inertia;

class WarehouseController extends Controller
{
    /**
     * Display the warehouse dashboard with live database records.
     */
    public function index()
    {
        $items = WarehouseItem::latest('updated_at')->get();
        $movements = StockMovement::latest()->take(20)->get()->map(function ($movement) {
            $badgeClass = match ($movement->type) {
                'IN' => 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20',
                'OUT' => 'bg-amber-500/10 text-amber-400 border-amber-500/20',
                default => 'bg-rose-500/10 text-rose-400 border-rose-500/20',
            };

            return [
                'id' => $movement->id,
                'type' => $movement->type,
                'code' => $movement->code,
                'itemName' => $movement->item_name,
                'quantity' => ($movement->type === 'IN' ? '+' : '-').$movement->amount.' '.$movement->unit,
                'sourceOrTarget' => $movement->source_or_target,
                'operator' => $movement->operator,
                'timestamp' => $movement->created_at ? $movement->created_at->diffForHumans() : 'Just now',
                'badgeClass' => $badgeClass,
            ];
        });

        // Hitung okupansi rak aktual berdasarkan data barang di database
        $racks = RackLocation::all()->map(function ($rack) use ($items) {
            $itemCountInRack = $items->filter(function ($item) use ($rack) {
                return str_contains(strtoupper($item->location), strtoupper($rack->code));
            })->sum('stock');

            $actualPercent = $itemCountInRack > 0 ? min(100, round(($itemCountInRack / 20) * 100)) : 0;
            $status = $actualPercent >= 80 ? 'high' : ($actualPercent > 0 ? 'normal' : 'low');

            return [
                'code' => $rack->code,
                'zone' => $rack->zone,
                'type' => $rack->type,
                'capacity' => $actualPercent.'%',
                'capacityNumber' => $actualPercent,
                'status' => $status,
            ];
        });

        $totalRacks = $racks->count();
        $averageOccupancy = $totalRacks > 0 ? round($racks->avg('capacityNumber')) : 0;
        $emptySlotsCount = $racks->where('capacityNumber', 0)->count();

        return Inertia::render('Dashboard/Dashboard', [
            'dbItems' => $items,
            'dbMovements' => $movements,
            'dbRacks' => $racks,
            'actualOccupancyPercent' => $averageOccupancy,
            'emptyRacksCount' => $emptySlotsCount,
        ]);
    }

    /**
     * Display the full System Activity Logs page.
     */
    public function logs(Request $request)
    {
        // Auto-reset / Prune: Hapus otomatis seluruh log yang berusia lebih dari 3 hari
        $threeDaysAgo = \Carbon\Carbon::now()->subDays(3);
        ActivityLog::where('created_at', '<', $threeDaysAgo)->delete();

        $query = ActivityLog::latest();

        if ($request->filled('type') && $request->type !== 'ALL') {
            $query->where('type', $request->type);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('user_name', 'like', "%{$search}%");
            });
        }

        $logs = $query->paginate(25)->withQueryString();

        return Inertia::render('Logs/Index', [
            'logs' => $logs,
            'filters' => [
                'type' => $request->type ?? 'ALL',
                'search' => $request->search ?? '',
            ],
            'metrics' => [
                'totalLogs' => ActivityLog::count(),
                'inboundCount' => ActivityLog::where('type', 'INBOUND')->count(),
                'outboundCount' => ActivityLog::where('type', 'OUTBOUND')->count(),
                'loginCount' => ActivityLog::where('type', 'LOGIN')->count(),
            ],
        ]);
    }

    /**
     * Store a newly created warehouse item/asset.
     */
    public function storeItem(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:50|unique:warehouse_items,code',
            'name' => 'required|string|max:255',
            'category' => 'required|string|max:100',
            'location' => 'required|string|max:255',
            'stock' => 'required|integer|min:0',
            'unit' => 'required|string|max:50',
            'min_stock' => 'required|integer|min:0',
            'status' => 'required|string|max:50',
            'condition' => 'required|string|max:50',
        ]);

        $item = WarehouseItem::create($validated);
        $user = auth()->user() ? auth()->user()->name : 'Petugas Terminal';

        // 1. Stock Movement
        StockMovement::create([
            'warehouse_item_id' => $item->id,
            'type' => 'IN',
            'code' => $item->code,
            'item_name' => $item->name,
            'amount' => $item->stock,
            'unit' => $item->unit,
            'source_or_target' => 'Pendaftaran Aset Baru ('.$item->location.')',
            'operator' => $user,
        ]);

        // 2. Global Activity Log
        ActivityLog::record(
            'INBOUND',
            "Registrasi Barang Baru: {$item->name} (+{$item->stock} {$item->unit})",
            "Kode: {$item->code} | Lokasi Rak: {$item->location} | Kategori: {$item->category}",
            $user,
            'emerald'
        );

        return redirect()->back()->with('success', 'Barang berhasil didaftarkan ke database.');
    }

    /**
     * Update rack location for an item.
     */
    public function updateLocation(Request $request, WarehouseItem $item)
    {
        $validated = $request->validate([
            'location' => 'required|string|max:255',
        ]);

        $oldLocation = $item->location;
        $item->update(['location' => $validated['location']]);
        $user = auth()->user() ? auth()->user()->name : 'Petugas Terminal';

        // 1. Stock Movement
        StockMovement::create([
            'warehouse_item_id' => $item->id,
            'type' => 'MAINT',
            'code' => $item->code,
            'item_name' => $item->name,
            'amount' => $item->stock,
            'unit' => $item->unit,
            'source_or_target' => "Relokasi: {$oldLocation} -> {$item->location}",
            'operator' => $user,
        ]);

        // 2. Global Activity Log
        ActivityLog::record(
            'MAINTENANCE',
            "Relokasi Rak Aset: {$item->name} ({$item->code})",
            "Dipindahkan dari [{$oldLocation}] menuju lokasi baru [{$item->location}]",
            $user,
            'purple'
        );

        return redirect()->back()->with('success', 'Lokasi rak berhasil diperbarui.');
    }

    /**
     * Process stock mutasi (Inbound or Outbound).
     */
    public function mutateStock(Request $request, WarehouseItem $item)
    {
        $validated = $request->validate([
            'type' => 'required|in:in,out',
            'amount' => 'required|integer|min:1',
            'notes' => 'nullable|string|max:255',
        ]);

        $amount = $validated['amount'];
        $notes = $validated['notes'];
        $user = auth()->user() ? auth()->user()->name : 'Petugas Terminal';

        if ($validated['type'] === 'in') {
            $item->stock += $amount;
            if ($item->stock > $item->min_stock && $item->status === 'Low Stock') {
                $item->status = 'Ready';
            }
            $item->save();

            StockMovement::create([
                'warehouse_item_id' => $item->id,
                'type' => 'IN',
                'code' => $item->code,
                'item_name' => $item->name,
                'amount' => $amount,
                'unit' => $item->unit,
                'source_or_target' => $notes ?: "Restock Inbound ke {$item->location}",
                'operator' => $user,
            ]);

            ActivityLog::record(
                'INBOUND',
                "Barang Masuk (Inbound): {$item->name} (+{$amount} {$item->unit})",
                "SKU: {$item->code} | Lokasi: {$item->location} | Catatan/PO: ".($notes ?: 'Restock Rutin'),
                $user,
                'emerald'
            );
        } else {
            if ($item->stock < $amount) {
                return redirect()->back()->withErrors(['amount' => 'Stok tidak mencukupi untuk pengeluaran.']);
            }

            $item->stock -= $amount;
            if ($item->stock <= $item->min_stock) {
                $item->status = 'Low Stock';
            }
            $item->save();

            StockMovement::create([
                'warehouse_item_id' => $item->id,
                'type' => 'OUT',
                'code' => $item->code,
                'item_name' => $item->name,
                'amount' => $amount,
                'unit' => $item->unit,
                'source_or_target' => $notes ?: "Pengeluaran Outbound dari {$item->location}",
                'operator' => $user,
            ]);

            ActivityLog::record(
                'OUTBOUND',
                "Barang Keluar (Outbound): {$item->name} (-{$amount} {$item->unit})",
                "SKU: {$item->code} | Dari: {$item->location} | Tujuan/Surat Jalan: ".($notes ?: 'Pengeluaran Logistik'),
                $user,
                'amber'
            );
        }

        return redirect()->back()->with('success', 'Mutasi stok berhasil disimpan.');
    }
}
