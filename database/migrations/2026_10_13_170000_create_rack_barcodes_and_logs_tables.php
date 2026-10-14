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
        // 1. Rack Barcodes Table
        Schema::create('rack_barcodes', function (Blueprint $table) {
            $table->id();
            $table->string('barcode_code')->unique();
            $table->string('barcode_type')->default('CODE128'); // CODE128 or QR_CODE
            $table->foreignId('rack_location_id')->nullable()->constrained('rack_locations')->nullOnDelete();
            $table->string('rack_code');
            $table->string('zone')->nullable();
            $table->string('label');
            $table->text('description')->nullable();
            $table->string('status')->default('ACTIVE'); // ACTIVE or INACTIVE
            $table->string('created_by')->default('Petugas Terminal');
            $table->timestamp('last_scanned_at')->nullable();
            $table->integer('scan_count')->default(0);
            $table->timestamps();
        });

        // 2. Barcode Activity & Scan Logs Table
        Schema::create('barcode_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rack_barcode_id')->nullable()->constrained('rack_barcodes')->nullOnDelete();
            $table->string('barcode_code');
            $table->string('rack_code')->nullable();
            $table->string('action'); // GENERATE, PRINT, SCAN, STATUS_CHANGE, DELETE
            $table->string('operator')->default('Petugas Terminal');
            $table->string('notes')->nullable();
            $table->string('ip_address')->nullable();
            $table->timestamp('created_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('barcode_logs');
        Schema::dropIfExists('rack_barcodes');
    }
};
