<?php

namespace Database\Seeders;

use App\Models\ActiveSession;
use App\Models\Activity;
use App\Models\NetworkDevice;
use App\Models\Package;
use App\Models\User;
use App\Models\Voucher;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Super Admin User
        $admin = User::updateOrCreate(
            ['email' => 'admin@livinghope.co.tz'],
            [
                'name' => 'Administrator',
                'password' => Hash::make('admin123'),
                'role' => 'admin',
                'business_name' => 'Living Hope Wi-Fi',
                'portal_slug' => 'living-hope',
                'phone' => '+255 712 000 111',
                'currency' => 'TZS',
                'portal_title' => 'LIVING HOPE WI-FI',
                'portal_welcome_text' => 'Welcome to Living Hope High-Speed Public Wi-Fi. Enter your voucher code below to activate your internet connection.',
                'portal_theme_color' => '#2563eb',
                'portal_logo_text' => 'LH',
                'portal_ssid' => 'LIVING_HOPE_WIFI',
                'email_verified_at' => now(),
            ]
        );

        // 2. Verified Hotspot Owner (Vendor)
        $vendor = User::updateOrCreate(
            ['email' => 'vendor@cafe.co.tz'],
            [
                'name' => 'David K. Mwalimu',
                'password' => Hash::make('password123'),
                'role' => 'vendor',
                'business_name' => 'Sunrise Beach Cafe',
                'portal_slug' => 'sunrise-cafe',
                'phone' => '+255 788 123 456',
                'currency' => 'TZS',
                'portal_title' => 'SUNRISE BEACH CAFE WI-FI',
                'portal_welcome_text' => 'Welcome to Sunrise Beach Cafe! Enjoy fresh coffee and lightning fast Wi-Fi. Enter your receipt voucher to connect.',
                'portal_theme_color' => '#059669',
                'portal_logo_text' => 'SC',
                'portal_ssid' => 'SUNRISE_CAFE_GUEST',
                'email_verified_at' => now(),
            ]
        );

        // 3. Staff / Cashier for Living Hope
        $staff = User::updateOrCreate(
            ['email' => 'cashier@livinghope.co.tz'],
            [
                'name' => 'Asha M.',
                'password' => Hash::make('password123'),
                'role' => 'staff',
                'business_name' => 'Living Hope Wi-Fi',
                'portal_slug' => 'living-hope-cashier',
                'phone' => '+255 754 111 222',
                'currency' => 'TZS',
                'parent_id' => $admin->id,
                'email_verified_at' => now(),
            ]
        );

        // 4. Unverified Hotspot Owner (for testing verification flow)
        $unverified = User::updateOrCreate(
            ['email' => 'newowner@example.com'],
            [
                'name' => 'Juma Rashid',
                'password' => Hash::make('password123'),
                'role' => 'vendor',
                'business_name' => 'Ocean View Lounge',
                'portal_slug' => 'ocean-view',
                'phone' => '+255 719 888 999',
                'currency' => 'TZS',
                'portal_title' => 'OCEAN VIEW LOUNGE WI-FI',
                'portal_welcome_text' => 'Welcome to Ocean View Lounge Wi-Fi. Enter your access code below.',
                'portal_theme_color' => '#8b5cf6',
                'portal_logo_text' => 'OV',
                'portal_ssid' => 'OCEAN_VIEW_GUEST',
                'email_verified_at' => null, // Unverified!
            ]
        );

        // Update existing packages to belong to admin if user_id is null
        Package::whereNull('user_id')->update(['user_id' => $admin->id]);
        Voucher::whereNull('user_id')->update(['user_id' => $admin->id]);
        NetworkDevice::whereNull('user_id')->update(['user_id' => $admin->id]);
        Activity::whereNull('user_id')->update(['user_id' => $admin->id]);

        // If admin has no packages, seed them
        if (Package::where('user_id', $admin->id)->count() === 0) {
            $pkg6h = Package::create([
                'user_id' => $admin->id,
                'name' => '6 Hours',
                'duration_hours' => 6,
                'price' => 500,
                'speed_limit' => '5 Mbps / 2 Mbps',
                'device_limit' => 1,
                'badge_label' => 'SHORT STAY',
                'icon' => '◷',
                'description' => 'Internet access for up to 6 hours after activation. Perfect for quick errands, messaging and browsing.',
                'is_active' => true,
            ]);

            $pkg24h = Package::create([
                'user_id' => $admin->id,
                'name' => '24 Hours',
                'duration_hours' => 24,
                'price' => 1000,
                'speed_limit' => '8 Mbps / 3 Mbps',
                'device_limit' => 1,
                'badge_label' => 'DAY PASS',
                'icon' => '☼',
                'description' => 'A full 24-hour access package for everyday browsing, studying, research and entertainment.',
                'is_active' => true,
            ]);

            $pkg7d = Package::create([
                'user_id' => $admin->id,
                'name' => '7 Days',
                'duration_hours' => 168,
                'price' => 6000,
                'speed_limit' => '10 Mbps / 4 Mbps',
                'device_limit' => 1,
                'badge_label' => 'POPULAR CHOICE',
                'icon' => '◈',
                'description' => 'Weekly unlimited access for regular users who need stable, consistent connectivity across several days.',
                'is_active' => true,
            ]);

            $pkg30d = Package::create([
                'user_id' => $admin->id,
                'name' => '30 Days',
                'duration_hours' => 720,
                'price' => 25000,
                'speed_limit' => '15 Mbps / 5 Mbps',
                'device_limit' => 2,
                'badge_label' => 'MONTHLY PASS',
                'icon' => '▣',
                'description' => 'Extended 30-day access for work, business, and streaming. Supports up to 2 devices simultaneously.',
                'is_active' => true,
            ]);

            $v1 = Voucher::create([
                'user_id' => $admin->id,
                'code' => 'LH-8K4P-2M7Q',
                'package_id' => $pkg6h->id,
                'amount' => 500,
                'customer_name' => 'Salim J.',
                'customer_phone' => '+255 712 *** 284',
                'status' => 'active',
                'cashier' => 'Asha M.',
                'activated_at' => Carbon::now()->subHours(2),
                'expires_at' => Carbon::now()->addHours(4),
                'mac_address' => '4A:2B:11:89:C4:02',
                'created_at' => Carbon::now()->setTime(12, 14),
            ]);

            ActiveSession::create([
                'voucher_id' => $v1->id,
                'ip_address' => '192.168.0.104',
                'mac_address' => '4A:2B:11:89:C4:02',
                'device_name' => 'Samsung Galaxy A34',
                'bytes_down' => 382400000,
                'bytes_up' => 45100000,
                'connected_at' => Carbon::now()->subHours(2),
                'status' => 'online',
            ]);

            NetworkDevice::create([
                'user_id' => $admin->id,
                'name' => 'TP-Link EAP110-Outdoor',
                'model' => 'EAP110-Outdoor v3',
                'ip_address' => '192.168.0.254',
                'mac_address' => 'D8:07:B6:34:2E:11',
                'location' => 'Main Courtyard Mast',
                'status' => 'online',
                'auth_mode' => 'External Portal / RADIUS',
                'notes' => 'Primary high-gain 300Mbps outdoor access point with Omada SDN management support.',
            ]);
        }

        // 5. Seed Sunrise Beach Cafe packages and vouchers for $vendor
        if (Package::where('user_id', $vendor->id)->count() === 0) {
            $vPkg1 = Package::create([
                'user_id' => $vendor->id,
                'name' => '1 Hour Coffee Pass',
                'duration_hours' => 1,
                'price' => 500,
                'speed_limit' => '10 Mbps / 5 Mbps',
                'device_limit' => 1,
                'badge_label' => 'QUICK BITE',
                'icon' => '☕',
                'description' => 'Fast speed for enjoying a coffee and checking emails or messages.',
                'is_active' => true,
            ]);

            $vPkg2 = Package::create([
                'user_id' => $vendor->id,
                'name' => '3 Hours Work & Chill',
                'duration_hours' => 3,
                'price' => 1500,
                'speed_limit' => '15 Mbps / 5 Mbps',
                'device_limit' => 1,
                'badge_label' => 'MOST POPULAR',
                'icon' => '☼',
                'description' => 'Ideal for freelancers and remote workers sipping drinks at the beach.',
                'is_active' => true,
            ]);

            $vPkg3 = Package::create([
                'user_id' => $vendor->id,
                'name' => 'Full Day Beach Pass',
                'duration_hours' => 24,
                'price' => 3000,
                'speed_limit' => '20 Mbps / 10 Mbps',
                'device_limit' => 2,
                'badge_label' => 'ALL DAY',
                'icon' => '◈',
                'description' => 'Full 24h unlimited connection for up to 2 devices.',
                'is_active' => true,
            ]);

            // Sample vouchers for vendor
            $scVoucher1 = Voucher::create([
                'user_id' => $vendor->id,
                'code' => 'SC-4F9K-7W2P',
                'package_id' => $vPkg2->id,
                'amount' => 1500,
                'customer_name' => 'Linda M.',
                'customer_phone' => '+255 765 *** 432',
                'status' => 'active',
                'cashier' => 'David K. Mwalimu',
                'activated_at' => Carbon::now()->subMinutes(45),
                'expires_at' => Carbon::now()->addMinutes(135),
                'mac_address' => '2C:54:91:88:AB:12',
            ]);

            ActiveSession::create([
                'voucher_id' => $scVoucher1->id,
                'ip_address' => '192.168.1.55',
                'mac_address' => '2C:54:91:88:AB:12',
                'device_name' => 'MacBook Air M2',
                'bytes_down' => 450000000,
                'bytes_up' => 52000000,
                'connected_at' => Carbon::now()->subMinutes(45),
                'status' => 'online',
            ]);

            Voucher::create([
                'user_id' => $vendor->id,
                'code' => 'SC-8B1T-9X4L',
                'package_id' => $vPkg1->id,
                'amount' => 500,
                'customer_name' => 'Bakari O.',
                'customer_phone' => '+255 714 *** 990',
                'status' => 'available',
                'cashier' => 'David K. Mwalimu',
            ]);

            NetworkDevice::create([
                'user_id' => $vendor->id,
                'name' => 'MikroTik hAP ac3 - Patio AP',
                'model' => 'RBD53iG-5HacD2HnD',
                'ip_address' => '192.168.1.1',
                'mac_address' => '74:4D:28:90:E1:FF',
                'location' => 'Patio Umbrella 4',
                'status' => 'online',
                'auth_mode' => 'MikroTik Hotspot + Captive Portal',
                'notes' => 'Covers the open terrace and beachfront sunbeds.',
            ]);

            Activity::create([
                'user_id' => $vendor->id,
                'icon' => '＋',
                'title' => 'Voucher sale recorded',
                'description' => '3 Hours Work & Chill · TZS 1,500 · Cashier: David K. Mwalimu',
                'type' => 'sale',
            ]);
        }
    }
}
