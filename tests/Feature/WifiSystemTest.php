<?php

namespace Tests\Feature;

use App\Models\Package;
use App\Models\User;
use App\Models\Voucher;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WifiSystemTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
        $this->admin = User::where('role', 'admin')->first();
    }

    public function test_dashboard_renders_successfully(): void
    {
        $response = $this->actingAs($this->admin)->get('/');
        $response->assertStatus(200);
        $response->assertSee('LIVING HOPE');
        $response->assertSee("Today's revenue", false);
    }

    public function test_vouchers_management_page_renders(): void
    {
        $response = $this->actingAs($this->admin)->get('/vouchers');
        $response->assertStatus(200);
        $response->assertSee('Voucher Management');
    }

    public function test_packages_page_renders(): void
    {
        $response = $this->actingAs($this->admin)->get('/packages');
        $response->assertStatus(200);
        $response->assertSee('Wi-Fi Packages');
    }

    public function test_sessions_page_renders(): void
    {
        $response = $this->actingAs($this->admin)->get('/sessions');
        $response->assertStatus(200);
        $response->assertSee('Active Sessions');
    }

    public function test_network_devices_page_renders(): void
    {
        $response = $this->actingAs($this->admin)->get('/devices');
        $response->assertStatus(200);
        $response->assertSee('TP-Link EAP110-Outdoor');
    }

    public function test_portal_page_renders(): void
    {
        $response = $this->get('/portal/living-hope');
        $response->assertStatus(200);
        $response->assertSee('LIVING HOPE');
    }

    public function test_sell_voucher_endpoint(): void
    {
        $pkg = Package::where('user_id', $this->admin->id)->first();
        $response = $this->actingAs($this->admin)->post('/vouchers/sell', [
            'package_id' => $pkg->id,
            'customer_phone' => '+255712999888',
            'customer_name' => 'Test Customer',
            'cashier' => 'Asha M.',
            'auto_activate' => 1,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('vouchers', [
            'customer_phone' => '+255712999888',
            'status' => 'active',
        ]);
    }

    public function test_batch_generate_vouchers(): void
    {
        $pkg = Package::where('user_id', $this->admin->id)->first();
        $response = $this->actingAs($this->admin)->post('/vouchers/batch', [
            'package_id' => $pkg->id,
            'quantity' => 5,
            'batch_tag' => 'AUTO-TEST',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('vouchers', [
            'batch_id' => 'AUTO-TEST-'.date('Ymd-Hi'),
        ]);
    }

    public function test_customer_portal_login(): void
    {
        $pkg = Package::where('user_id', $this->admin->id)->first();
        $voucher = Voucher::create([
            'user_id' => $this->admin->id,
            'code' => Voucher::generateCode('LH'),
            'package_id' => $pkg->id,
            'amount' => $pkg->price,
            'status' => 'available',
        ]);

        $response = $this->post('/portal/connect', [
            'voucher_code' => $voucher->code,
            'mac_address' => '4A:2B:11:89:C4:02',
        ]);

        $response->assertStatus(200);
        $response->assertSee('You are Connected!');
        $this->assertDatabaseHas('vouchers', [
            'id' => $voucher->id,
            'status' => 'active',
        ]);
    }

    public function test_export_vouchers_csv(): void
    {
        $response = $this->actingAs($this->admin)->get('/export/vouchers');
        $response->assertStatus(200);
        $this->assertStringContainsString('text/csv', $response->headers->get('Content-Type'));
    }
}
