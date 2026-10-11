<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Rack Locations / Zones
        Schema::create('rack_locations', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique(); // e.g. A-01, B-02
            $table->string('zone');           // e.g. Zone A, Zone B
            $table->string('type');           // e.g. Pallet Rack, Shelving Unit, Hazmat / Cold
            $table->integer('capacity_percent')->default(0);
            $table->string('status')->default('normal'); // normal, high, low
            $table->timestamps();
        });

        // 2. Warehouse Items / Assets
        Schema::create('warehouse_items', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique(); // SKU / Asset Code (e.g. AST-FLK-001)
            $table->string('name');           // Item / Asset Name
            $table->string('category');       // Heavy Equipment, Material Handlers, etc.
            $table->string('location');       // Teks Lokasi Rak fisik (e.g. Zone A (A-01) Lantai 1)
            $table->integer('stock')->default(0);
            $table->string('unit')->default('Unit'); // Unit, Pcs, Box, Roll
            $table->integer('min_stock')->default(2);
            $table->string('status')->default('Ready'); // Ready, Low Stock, In Maintenance
            $table->string('condition')->default('Good'); // Good, New, Under Repair
            $table->timestamps();
        });

        // 3. Stock Movements (Inbound / Outbound / Maintenance Mutasi)
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('warehouse_item_id')->nullable()->constrained('warehouse_items')->nullOnDelete();
            $table->string('type'); // IN, OUT, MAINT
            $table->string('code'); // Snapshot of item code
            $table->string('item_name'); // Snapshot of item name
            $table->integer('amount');   // Positive integer
            $table->string('unit')->default('Unit');
            $table->string('source_or_target'); // Tujuan / Supplier / Keterangan
            $table->string('operator'); // Nama user / operator
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
        Schema::dropIfExists('warehouse_items');
        Schema::dropIfExists('rack_locations');
    }
};
