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
        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->default('vendor')->after('email'); // admin, vendor, staff
            $table->string('business_name')->nullable()->after('role');
            $table->string('portal_slug')->nullable()->unique()->after('business_name');
            $table->string('phone')->nullable()->after('portal_slug');
            $table->string('currency')->default('TZS')->after('phone');
            $table->string('portal_title')->nullable()->after('currency');
            $table->text('portal_welcome_text')->nullable()->after('portal_title');
            $table->string('portal_theme_color')->default('#2563eb')->after('portal_welcome_text');
            $table->string('portal_logo_text')->default('LH')->after('portal_theme_color');
            $table->string('portal_ssid')->default('LIVING_HOPE_WIFI')->after('portal_logo_text');
            $table->foreignId('parent_id')->nullable()->constrained('users')->nullOnDelete()->after('portal_ssid');
        });

        Schema::table('packages', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->after('id')->constrained('users')->cascadeOnDelete();
        });

        Schema::table('vouchers', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->after('id')->constrained('users')->cascadeOnDelete();
        });

        Schema::table('network_devices', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->after('id')->constrained('users')->cascadeOnDelete();
        });

        Schema::table('activities', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->after('id')->constrained('users')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('activities', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');
        });

        Schema::table('network_devices', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');
        });

        Schema::table('vouchers', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');
        });

        Schema::table('packages', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['parent_id']);
            $table->dropColumn([
                'role',
                'business_name',
                'portal_slug',
                'phone',
                'currency',
                'portal_title',
                'portal_welcome_text',
                'portal_theme_color',
                'portal_logo_text',
                'portal_ssid',
                'parent_id',
            ]);
        });
    }
};
