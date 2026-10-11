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
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->string('type'); // 'INBOUND', 'OUTBOUND', 'LOGIN', 'LOGOUT', 'MAINTENANCE', 'SYSTEM'
            $table->string('user_name'); // Nama pengguna atau operator
            $table->string('title'); // Judul ringkas aktivitas
            $table->text('description')->nullable(); // Detail lengkap
            $table->string('ip_address')->nullable();
            $table->string('badge_color')->default('blue'); // 'emerald', 'amber', 'rose', 'blue', 'purple'
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};
