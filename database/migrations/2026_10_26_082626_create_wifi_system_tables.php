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
        Schema::create('packages', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->integer('duration_hours');
            $table->unsignedInteger('price'); // in TZS
            $table->string('speed_limit')->default('5 Mbps / 2 Mbps');
            $table->unsignedSmallInteger('device_limit')->default(1);
            $table->string('badge_label')->default('PASS');
            $table->string('icon')->default('◈');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('vouchers', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->foreignId('package_id')->constrained('packages')->cascadeOnDelete();
            $table->unsignedInteger('amount');
            $table->string('customer_name')->nullable();
            $table->string('customer_phone')->nullable();
            $table->string('status')->default('available'); // available, active, expired, revoked
            $table->string('cashier')->nullable()->default('Asha M.');
            $table->string('batch_id')->nullable();
            $table->timestamp('activated_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->string('mac_address')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('active_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('voucher_id')->constrained('vouchers')->cascadeOnDelete();
            $table->string('ip_address')->default('192.168.0.101');
            $table->string('mac_address')->default('AA:BB:CC:DD:EE:01');
            $table->string('device_name')->nullable()->default('Mobile Device');
            $table->unsignedBigInteger('bytes_down')->default(0);
            $table->unsignedBigInteger('bytes_up')->default(0);
            $table->timestamp('connected_at')->useCurrent();
            $table->string('status')->default('online');
            $table->timestamps();
        });

        Schema::create('network_devices', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('model')->default('EAP110-Outdoor');
            $table->string('ip_address')->default('192.168.0.254');
            $table->string('mac_address')->nullable();
            $table->string('location')->nullable();
            $table->string('status')->default('online'); // online, offline, testing
            $table->string('auth_mode')->default('Captive Portal / RADIUS');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('activities', function (Blueprint $table) {
            $table->id();
            $table->string('icon')->default('＋');
            $table->string('title');
            $table->text('description');
            $table->string('type')->default('sale');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activities');
        Schema::dropIfExists('network_devices');
        Schema::dropIfExists('active_sessions');
        Schema::dropIfExists('vouchers');
        Schema::dropIfExists('packages');
    }
};
