<?php

namespace Database\Seeders;

use App\Models\RackLocation;
use App\Models\StockMovement;
use App\Models\WarehouseItem;
use Illuminate\Database\Seeder;

class WarehouseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Racks
        $racks = [
            ['code' => 'A-01', 'zone' => 'Zone A', 'type' => 'Pallet Rack', 'capacity_percent' => 85, 'status' => 'high'],
            ['code' => 'A-02', 'zone' => 'Zone A', 'type' => 'Pallet Rack', 'capacity_percent' => 60, 'status' => 'normal'],
            ['code' => 'B-01', 'zone' => 'Zone B', 'type' => 'Shelving Unit', 'capacity_percent' => 50, 'status' => 'normal'],
            ['code' => 'B-02', 'zone' => 'Zone B', 'type' => 'Shelving Unit', 'capacity_percent' => 40, 'status' => 'normal'],
            ['code' => 'C-01', 'zone' => 'Zone C', 'type' => 'Chemical / Paint Storage', 'capacity_percent' => 70, 'status' => 'normal'],
            ['code' => 'C-02', 'zone' => 'Zone C', 'type' => 'Chemical / Paint Storage', 'capacity_percent' => 30, 'status' => 'low'],
        ];

        foreach ($racks as $rack) {
            RackLocation::updateOrCreate(['code' => $rack['code']], $rack);
        }

        // Hapus item demo lama jika ada
        WarehouseItem::whereIn('code', [
            'AST-FLK-001', 'AST-PLJ-042', 'INV-HLM-109', 'AST-SCN-018', 'INV-ROL-502', 'INV-STP-311'
        ])->delete();

        // 2. Items Sesuai Katalog Resmi (Bahan Baku, Painting, Masking, Buffing, Packaging)
        $items = [
            [
                'code' => 'RM-001',
                'name' => 'Side Body Molding',
                'category' => 'Bahan Baku',
                'location' => 'Zone A (A-01) Rak 1',
                'stock' => 150,
                'unit' => 'Pcs',
                'base_unit' => 'Pcs',
                'packaging_unit' => 'Box',
                'conversion_factor' => 25,
                'min_stock' => 30,
                'status' => 'Ready',
                'condition' => 'Good',
            ],
            [
                'code' => 'RM-002',
                'name' => 'Cover Body',
                'category' => 'Bahan Baku',
                'location' => 'Zone A (A-01) Rak 2',
                'stock' => 85,
                'unit' => 'Pcs',
                'base_unit' => 'Pcs',
                'packaging_unit' => null,
                'conversion_factor' => 1,
                'min_stock' => 20,
                'status' => 'Ready',
                'condition' => 'Good',
            ],
            [
                'code' => 'PT-001',
                'name' => 'Cat Primer',
                'category' => 'Painting',
                'location' => 'Zone C (C-01) Rak 1',
                'stock' => 40,
                'unit' => 'Liter',
                'base_unit' => 'Liter',
                'packaging_unit' => 'Pail',
                'conversion_factor' => 20,
                'min_stock' => 10,
                'status' => 'Ready',
                'condition' => 'Good',
            ],
            [
                'code' => 'PT-002',
                'name' => 'Cat Top Coat',
                'category' => 'Painting',
                'location' => 'Zone C (C-01) Rak 2',
                'stock' => 60,
                'unit' => 'Liter',
                'base_unit' => 'Liter',
                'packaging_unit' => 'Pail',
                'conversion_factor' => 20,
                'min_stock' => 15,
                'status' => 'Ready',
                'condition' => 'Good',
            ],
            [
                'code' => 'PT-003',
                'name' => 'Thinner',
                'category' => 'Painting',
                'location' => 'Zone C (C-02) Rak 1',
                'stock' => 25,
                'unit' => 'Liter',
                'base_unit' => 'Liter',
                'packaging_unit' => 'Jerigen',
                'conversion_factor' => 5,
                'min_stock' => 8,
                'status' => 'Ready',
                'condition' => 'Good',
            ],
            [
                'code' => 'MS-001',
                'name' => 'Masking Tape',
                'category' => 'Masking',
                'location' => 'Zone B (B-01) Rak 1',
                'stock' => 120,
                'unit' => 'Roll',
                'base_unit' => 'Roll',
                'packaging_unit' => 'Karton',
                'conversion_factor' => 24,
                'min_stock' => 25,
                'status' => 'Ready',
                'condition' => 'Good',
            ],
            [
                'code' => 'MS-002',
                'name' => 'Masking Plastic',
                'category' => 'Masking',
                'location' => 'Zone B (B-01) Rak 2',
                'stock' => 45,
                'unit' => 'Roll',
                'base_unit' => 'Roll',
                'packaging_unit' => null,
                'conversion_factor' => 1,
                'min_stock' => 10,
                'status' => 'Ready',
                'condition' => 'Good',
            ],
            [
                'code' => 'BF-001',
                'name' => 'Amplas',
                'category' => 'Buffing',
                'location' => 'Zone B (B-02) Rak 1',
                'stock' => 300,
                'unit' => 'Pcs',
                'base_unit' => 'Pcs',
                'packaging_unit' => 'Pack',
                'conversion_factor' => 50,
                'min_stock' => 50,
                'status' => 'Ready',
                'condition' => 'Good',
            ],
            [
                'code' => 'BF-002',
                'name' => 'Compound',
                'category' => 'Buffing',
                'location' => 'Zone B (B-02) Rak 2',
                'stock' => 35,
                'unit' => 'Botol',
                'base_unit' => 'Botol',
                'packaging_unit' => 'Karton',
                'conversion_factor' => 12,
                'min_stock' => 8,
                'status' => 'Ready',
                'condition' => 'Good',
            ],
            [
                'code' => 'PK-001',
                'name' => 'Plastik Packing',
                'category' => 'Packaging',
                'location' => 'Zone A (A-02) Rak 1',
                'stock' => 50,
                'unit' => 'Roll',
                'base_unit' => 'Roll',
                'packaging_unit' => null,
                'conversion_factor' => 1,
                'min_stock' => 10,
                'status' => 'Ready',
                'condition' => 'Good',
            ],
            [
                'code' => 'PK-002',
                'name' => 'Kardus Packaging',
                'category' => 'Packaging',
                'location' => 'Zone A (A-02) Rak 2',
                'stock' => 200,
                'unit' => 'Pcs',
                'base_unit' => 'Pcs',
                'packaging_unit' => 'Ikat',
                'conversion_factor' => 20,
                'min_stock' => 40,
                'status' => 'Ready',
                'condition' => 'Good',
            ],
        ];

        foreach ($items as $itemData) {
            $created = WarehouseItem::updateOrCreate(['code' => $itemData['code']], $itemData);

            // Seed initial movement
            StockMovement::create([
                'warehouse_item_id' => $created->id,
                'type' => 'IN',
                'code' => $created->code,
                'item_name' => $created->name,
                'amount' => $created->stock,
                'unit' => $created->unit,
                'source_or_target' => 'Initial Stock Opname ('.$created->location.')',
                'operator' => 'Sistem Warehouse',
            ]);
        }
    }
}
