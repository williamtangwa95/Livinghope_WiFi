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
        if (! Schema::hasTable('voucher_devices')) {
            Schema::create('voucher_devices', function (Blueprint $table) {
                $table->id();
                $table->foreignId('voucher_id')->constrained('vouchers')->cascadeOnDelete();
                $table->string('mac_address');
                $table->string('ip_address')->nullable();
                $table->string('device_name')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamp('first_authorized_at')->useCurrent();
                $table->timestamp('last_seen_at')->useCurrent();
                $table->timestamps();

                $table->unique(['voucher_id', 'mac_address']);
                $table->index(['voucher_id', 'is_active']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('voucher_devices');
    }
};
