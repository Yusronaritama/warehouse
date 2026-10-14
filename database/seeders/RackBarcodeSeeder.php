<?php

namespace Database\Seeders;

use App\Models\BarcodeLog;
use App\Models\RackBarcode;
use App\Models\RackLocation;
use Illuminate\Database\Seeder;

class RackBarcodeSeeder extends Seeder
{
    public function run(): void
    {
        $rackA = RackLocation::where('code', 'A-01')->first();
        $rackB = RackLocation::where('code', 'B-01')->first();
        $rackC = RackLocation::where('code', 'C-01')->first();

        $bc1 = RackBarcode::firstOrCreate(
            ['barcode_code' => 'BC-A01-1024'],
            [
                'barcode_type' => 'CODE128',
                'rack_location_id' => $rackA?->id,
                'rack_code' => 'A-01',
                'zone' => 'Zone A',
                'label' => 'Rak Pallet Utama Heavy Equipment',
                'description' => 'Area forklift dan pallet jack lantai 1',
                'status' => 'ACTIVE',
                'created_by' => 'Supervisor Logistik',
                'scan_count' => 14,
                'last_scanned_at' => now()->subHours(2),
            ]
        );

        $bc2 = RackBarcode::firstOrCreate(
            ['barcode_code' => 'QR-B01-4421'],
            [
                'barcode_type' => 'QR_CODE',
                'rack_location_id' => $rackB?->id,
                'rack_code' => 'B-01',
                'zone' => 'Zone B',
                'label' => 'Shelving Safety Gear & APD',
                'description' => 'Rak bertingkat APD dan helm industri',
                'status' => 'ACTIVE',
                'created_by' => 'Operator Gudang 1',
                'scan_count' => 8,
                'last_scanned_at' => now()->subHours(5),
            ]
        );

        $bc3 = RackBarcode::firstOrCreate(
            ['barcode_code' => 'BC-C01-8890'],
            [
                'barcode_type' => 'CODE128',
                'rack_location_id' => $rackC?->id,
                'rack_code' => 'C-01',
                'zone' => 'Zone C',
                'label' => 'Bin Spare Parts & Conveyor Roller',
                'description' => 'Komponen mekanik dan rantai konveyor',
                'status' => 'ACTIVE',
                'created_by' => 'Teknisi Maintenance',
                'scan_count' => 3,
                'last_scanned_at' => now()->subDay(),
            ]
        );

        BarcodeLog::record('GENERATE', $bc1->barcode_code, $bc1->rack_code, 'Barcode rak didaftarkan: Rak Pallet Utama Heavy Equipment', $bc1->id, 'Supervisor Logistik');
        BarcodeLog::record('SCAN', $bc1->barcode_code, $bc1->rack_code, 'Pemindaian rutin stock opname', $bc1->id, 'Operator Lapangan');
        BarcodeLog::record('GENERATE', $bc2->barcode_code, $bc2->rack_code, 'Barcode QR didaftarkan: Shelving Safety Gear & APD', $bc2->id, 'Operator Gudang 1');
        BarcodeLog::record('PRINT', $bc2->barcode_code, $bc2->rack_code, 'Stiker barcode dicetak untuk penempelan tiang rak B-01', $bc2->id, 'Supervisor Logistik');
        BarcodeLog::record('GENERATE', $bc3->barcode_code, $bc3->rack_code, 'Barcode rak didaftarkan: Bin Spare Parts', $bc3->id, 'Teknisi Maintenance');
    }
}
