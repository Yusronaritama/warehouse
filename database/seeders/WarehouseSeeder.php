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
            ['code' => 'A-01', 'zone' => 'Zone A', 'type' => 'Pallet Rack', 'capacity_percent' => 90, 'status' => 'high'],
            ['code' => 'A-02', 'zone' => 'Zone A', 'type' => 'Pallet Rack', 'capacity_percent' => 60, 'status' => 'normal'],
            ['code' => 'B-01', 'zone' => 'Zone B', 'type' => 'Shelving Unit', 'capacity_percent' => 45, 'status' => 'normal'],
            ['code' => 'B-02', 'zone' => 'Zone B', 'type' => 'Shelving Unit', 'capacity_percent' => 15, 'status' => 'low'],
            ['code' => 'C-01', 'zone' => 'Zone C', 'type' => 'Hazmat / Cold', 'capacity_percent' => 78, 'status' => 'normal'],
            ['code' => 'C-02', 'zone' => 'Zone C', 'type' => 'Hazmat / Cold', 'capacity_percent' => 95, 'status' => 'high'],
        ];

        foreach ($racks as $rack) {
            RackLocation::updateOrCreate(['code' => $rack['code']], $rack);
        }

        // 2. Items
        $items = [
            [
                'code' => 'AST-FLK-001',
                'name' => 'Electric Forklift 2.5 Ton (Toyota 8FBN25)',
                'category' => 'Heavy Equipment',
                'location' => 'Zone A (A-01) Lantai 1',
                'stock' => 3,
                'unit' => 'Unit',
                'min_stock' => 2,
                'status' => 'Ready',
                'condition' => 'Good',
            ],
            [
                'code' => 'AST-PLJ-042',
                'name' => 'Manual Hydraulic Pallet Jack 3000kg',
                'category' => 'Material Handlers',
                'location' => 'Zone A (A-02) Koridor 4',
                'stock' => 12,
                'unit' => 'Unit',
                'min_stock' => 5,
                'status' => 'Ready',
                'condition' => 'Good',
            ],
            [
                'code' => 'INV-HLM-109',
                'name' => 'Industrial Safety Helmet ANSI Z89.1',
                'category' => 'Safety Gear',
                'location' => 'Zone B (B-01) Rak Tingkat 3',
                'stock' => 4,
                'unit' => 'Box (20 pcs)',
                'min_stock' => 10,
                'status' => 'Low Stock',
                'condition' => 'New',
            ],
            [
                'code' => 'AST-SCN-018',
                'name' => 'Honeywell Barcode Scanner Wireless 2D',
                'category' => 'Electronics',
                'location' => 'Zone B (B-02) Lemari IT-01',
                'stock' => 8,
                'unit' => 'Unit',
                'min_stock' => 3,
                'status' => 'In Maintenance',
                'condition' => 'Under Repair',
            ],
            [
                'code' => 'INV-ROL-502',
                'name' => 'Heavy Duty Conveyor Roller 50x500mm',
                'category' => 'Spare Parts',
                'location' => 'Zone C (C-01) Bin R-12',
                'stock' => 45,
                'unit' => 'Pcs',
                'min_stock' => 15,
                'status' => 'Ready',
                'condition' => 'New',
            ],
            [
                'code' => 'INV-STP-311',
                'name' => 'Polypropylene Strapping Band 15mm Roll',
                'category' => 'Material Handlers',
                'location' => 'Zone A (A-01) Rak Tingkat 1',
                'stock' => 2,
                'unit' => 'Roll',
                'min_stock' => 5,
                'status' => 'Low Stock',
                'condition' => 'New',
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
