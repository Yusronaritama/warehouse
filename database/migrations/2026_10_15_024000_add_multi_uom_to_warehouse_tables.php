<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('warehouse_items', function (Blueprint $table) {
            $table->string('base_unit')->default('Unit')->after('stock');
            $table->string('packaging_unit')->nullable()->after('base_unit');
            $table->integer('conversion_factor')->default(1)->after('packaging_unit');
        });

        Schema::table('stock_movements', function (Blueprint $table) {
            $table->integer('input_amount')->nullable()->after('amount');
            $table->string('input_unit')->nullable()->after('input_amount');
        });

        // Backfill existing rows with initial base_unit and conversion
        DB::table('warehouse_items')->get()->each(function ($item) {
            $unit = $item->unit ?: 'Unit';
            $baseUnit = $unit;
            $packagingUnit = null;
            $factor = 1;
            $stock = $item->stock;

            // Check if existing unit had format like "Box (20 pcs)"
            if (preg_match('/^([a-zA-Z]+)\s*\(\s*(\d+)\s*([a-zA-Z]+)\s*\)$/i', $unit, $matches)) {
                $packagingUnit = ucfirst($matches[1]);
                $factor = (int) $matches[2];
                $baseUnit = ucfirst($matches[3]);
                // Convert stock to base unit (e.g. 4 Box @ 20 pcs = 80 pcs)
                $stock = $stock * $factor;
            }

            DB::table('warehouse_items')->where('id', $item->id)->update([
                'base_unit' => $baseUnit,
                'packaging_unit' => $packagingUnit,
                'conversion_factor' => $factor,
                'stock' => $stock,
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stock_movements', function (Blueprint $table) {
            $table->dropColumn(['input_amount', 'input_unit']);
        });

        Schema::table('warehouse_items', function (Blueprint $table) {
            $table->dropColumn(['base_unit', 'packaging_unit', 'conversion_factor']);
        });
    }
};
