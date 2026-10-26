<?php

namespace Tests\Feature;

use App\Contracts\HotspotGatewayInterface;
use App\Models\ActiveSession;
use App\Models\Package;
use App\Models\User;
use App\Models\Voucher;
use Carbon\Carbon;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class SimulatedGatewayTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected User $vendor;

    protected HotspotGatewayInterface $gateway;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);

        $this->admin = User::where('role', 'admin')->first();
        $this->vendor = User::where('role', 'vendor')->first();
        $this->gateway = app(HotspotGatewayInterface::class);
    }

    /**
     * Test 1: Valid voucher authentication.
     */
    public function test_1_valid_voucher_authentication(): void
    {
        $package = Package::where('user_id', $this->admin->id)->first();
        $voucher = Voucher::create([
            'user_id' => $this->admin->id,
            'code' => Voucher::generateCode('LH'),
            'package_id' => $package->id,
            'amount' => $package->price,
            'status' => 'available',
        ]);

        $deviceContext = [
            'mac_address' => '4A:2B:11:89:C4:02',
            'ip_address' => '192.168.0.150',
            'device_name' => 'Test Mobile Handset',
            'host_id' => $this->admin->id,
        ];

        $result = $this->gateway->authenticate($voucher->code, $deviceContext);

        $this->assertTrue($result->isSuccess());
        $this->assertDatabaseHas('vouchers', [
            'id' => $voucher->id,
            'status' => 'active',
        ]);
        $this->assertDatabaseHas('voucher_devices', [
            'voucher_id' => $voucher->id,
            'mac_address' => '4A:2B:11:89:C4:02',
            'is_active' => 1,
        ]);
        $this->assertDatabaseHas('active_sessions', [
            'voucher_id' => $voucher->id,
            'mac_address' => '4A:2B:11:89:C4:02',
            'status' => 'online',
        ]);
    }

    /**
     * Test 2: Invalid voucher rejection.
     */
    public function test_2_invalid_voucher_rejection(): void
    {
        $deviceContext = [
            'mac_address' => '4A:2B:11:89:C4:02',
            'ip_address' => '192.168.0.150',
            'device_name' => 'Test Handset',
            'host_id' => $this->admin->id,
        ];

        $result = $this->gateway->authenticate('NON-EXISTENT-CODE', $deviceContext);

        $this->assertFalse($result->isSuccess());
        $this->assertEquals('VOUCHER_NOT_FOUND', $result->errorCode);
    }

    /**
     * Test 3: Expired voucher rejection.
     */
    public function test_3_expired_voucher_rejection(): void
    {
        $package = Package::where('user_id', $this->admin->id)->first();
        $voucher = Voucher::create([
            'user_id' => $this->admin->id,
            'code' => Voucher::generateCode('LH'),
            'package_id' => $package->id,
            'amount' => $package->price,
            'status' => 'active',
            'activated_at' => Carbon::now()->subHours(10),
            'expires_at' => Carbon::now()->subHours(2), // Already expired
        ]);

        $deviceContext = [
            'mac_address' => '4A:2B:11:89:C4:02',
            'ip_address' => '192.168.0.150',
            'host_id' => $this->admin->id,
        ];

        $result = $this->gateway->authenticate($voucher->code, $deviceContext);

        $this->assertFalse($result->isSuccess());
        $this->assertEquals('VOUCHER_EXPIRED', $result->errorCode);
    }

    /**
     * Test 4: Duplicate single-use voucher activation rejected for different device.
     */
    public function test_4_duplicate_voucher_concurrent_activation(): void
    {
        $package = Package::where('user_id', $this->admin->id)->where('device_limit', 1)->first();
        $voucher = Voucher::create([
            'user_id' => $this->admin->id,
            'code' => Voucher::generateCode('LH'),
            'package_id' => $package->id,
            'amount' => $package->price,
            'status' => 'available',
        ]);

        // First activation by Device 1
        $res1 = $this->gateway->authenticate($voucher->code, [
            'mac_address' => '11:22:33:44:55:01',
            'ip_address' => '192.168.0.101',
            'host_id' => $this->admin->id,
        ]);
        $this->assertTrue($res1->isSuccess());

        // Second activation attempt with different Device 2
        $res2 = $this->gateway->authenticate($voucher->code, [
            'mac_address' => '11:22:33:44:55:02',
            'ip_address' => '192.168.0.102',
            'host_id' => $this->admin->id,
        ]);

        $this->assertFalse($res2->isSuccess());
        $this->assertEquals('DEVICE_LIMIT_EXCEEDED', $res2->errorCode);
    }

    /**
     * Test 5: Device-limit enforcement for single device (device_limit = 1).
     */
    public function test_5_device_limit_enforcement_single_device(): void
    {
        $package = Package::create([
            'user_id' => $this->admin->id,
            'name' => '1 Device Strict Package',
            'duration_hours' => 2,
            'price' => 500,
            'device_limit' => 1,
            'is_active' => true,
        ]);

        $voucher = Voucher::create([
            'user_id' => $this->admin->id,
            'code' => Voucher::generateCode('LH'),
            'package_id' => $package->id,
            'amount' => $package->price,
            'status' => 'available',
        ]);

        // Device A connects
        $this->assertTrue($this->gateway->checkDeviceLimit($voucher, 'AA:BB:CC:DD:EE:01'));
        $this->gateway->authenticate($voucher->code, [
            'mac_address' => 'AA:BB:CC:DD:EE:01',
            'host_id' => $this->admin->id,
        ]);

        // Device A reconnecting is allowed
        $this->assertTrue($this->gateway->checkDeviceLimit($voucher->fresh(), 'AA:BB:CC:DD:EE:01'));

        // Device B connecting is denied on server
        $this->assertFalse($this->gateway->checkDeviceLimit($voucher->fresh(), 'AA:BB:CC:DD:EE:02'));
    }

    /**
     * Test 6: Device-limit enforcement for multi-device packages (device_limit = 2).
     */
    public function test_6_device_limit_enforcement_multi_device(): void
    {
        $package = Package::create([
            'user_id' => $this->admin->id,
            'name' => '2 Devices Shared Package',
            'duration_hours' => 24,
            'price' => 2000,
            'device_limit' => 2,
            'is_active' => true,
        ]);

        $voucher = Voucher::create([
            'user_id' => $this->admin->id,
            'code' => Voucher::generateCode('LH'),
            'package_id' => $package->id,
            'amount' => $package->price,
            'status' => 'available',
        ]);

        // Device 1 connects: allowed
        $res1 = $this->gateway->authenticate($voucher->code, [
            'mac_address' => 'AA:BB:CC:00:00:01',
            'host_id' => $this->admin->id,
        ]);
        $this->assertTrue($res1->isSuccess());

        // Device 2 connects: allowed
        $res2 = $this->gateway->authenticate($voucher->code, [
            'mac_address' => 'AA:BB:CC:00:00:02',
            'host_id' => $this->admin->id,
        ]);
        $this->assertTrue($res2->isSuccess());
        $this->assertEquals(2, $voucher->fresh()->devices()->count());

        // Device 3 connects: rejected
        $res3 = $this->gateway->authenticate($voucher->code, [
            'mac_address' => 'AA:BB:CC:00:00:03',
            'host_id' => $this->admin->id,
        ]);
        $this->assertFalse($res3->isSuccess());
        $this->assertEquals('DEVICE_LIMIT_EXCEEDED', $res3->errorCode);
    }

    /**
     * Test 7: Session expiry.
     */
    public function test_7_session_expiry_synchronization(): void
    {
        $package = Package::where('user_id', $this->admin->id)->first();
        $voucher = Voucher::create([
            'user_id' => $this->admin->id,
            'code' => Voucher::generateCode('LH'),
            'package_id' => $package->id,
            'amount' => $package->price,
            'status' => 'active',
            'activated_at' => Carbon::now()->subHours(8),
            'expires_at' => Carbon::now()->subHours(1),
        ]);

        $session = ActiveSession::create([
            'voucher_id' => $voucher->id,
            'ip_address' => '192.168.0.120',
            'mac_address' => '99:88:77:66:55:44',
            'status' => 'online',
        ]);

        $expired = $this->gateway->syncExpiredSessions();

        $this->assertGreaterThanOrEqual(1, $expired);
        $this->assertEquals('expired', $voucher->fresh()->status);
        $this->assertEquals('terminated', $session->fresh()->status);
    }

    /**
     * Test 8: Session termination & disconnect.
     */
    public function test_8_session_termination_and_disconnect(): void
    {
        $package = Package::where('user_id', $this->admin->id)->first();
        $voucher = Voucher::create([
            'user_id' => $this->admin->id,
            'code' => Voucher::generateCode('LH'),
            'package_id' => $package->id,
            'amount' => $package->price,
            'status' => 'available',
        ]);

        $this->gateway->authenticate($voucher->code, [
            'mac_address' => '77:66:55:44:33:22',
            'ip_address' => '192.168.0.133',
            'host_id' => $this->admin->id,
        ]);

        $session = ActiveSession::where('voucher_id', $voucher->id)->first();
        $this->assertNotNull($session);
        $this->assertEquals('online', $session->status);

        $disconnected = $this->gateway->disconnectSession($session);

        $this->assertTrue($disconnected);
        $this->assertEquals('terminated', $session->fresh()->status);
    }

    /**
     * Test 9: Persistence of voucher, device, and sales activity records.
     */
    public function test_9_persistence_of_voucher_and_sales_records(): void
    {
        $package = Package::where('user_id', $this->admin->id)->first();
        $voucher = Voucher::create([
            'user_id' => $this->admin->id,
            'code' => Voucher::generateCode('LH'),
            'package_id' => $package->id,
            'amount' => $package->price,
            'status' => 'available',
        ]);

        $this->gateway->authenticate($voucher->code, [
            'mac_address' => '33:44:55:66:77:88',
            'ip_address' => '192.168.0.144',
            'device_name' => 'Office Laptop',
            'host_id' => $this->admin->id,
        ]);

        $this->assertDatabaseHas('voucher_devices', [
            'voucher_id' => $voucher->id,
            'mac_address' => '33:44:55:66:77:88',
            'device_name' => 'Office Laptop',
        ]);

        $this->assertDatabaseHas('activities', [
            'user_id' => $this->admin->id,
            'type' => 'portal',
        ]);
    }

    /**
     * Test 10: Gateway errors and unavailable services resilience.
     */
    public function test_10_gateway_error_and_unavailable_service(): void
    {
        Config::set('hotspot.simulate_failure', true);

        $deviceContext = [
            'mac_address' => 'AA:BB:CC:DD:EE:99',
            'ip_address' => '192.168.0.199',
            'host_id' => $this->admin->id,
        ];

        $result = $this->gateway->authenticate('ANY-CODE', $deviceContext);

        $this->assertFalse($result->isSuccess());
        $this->assertEquals('GATEWAY_UNAVAILABLE', $result->errorCode);

        Config::set('hotspot.simulate_failure', false);
    }

    /**
     * Test 11: Portal HTTP request and sales flow integration.
     */
    public function test_11_portal_http_connect_flow(): void
    {
        $package = Package::where('user_id', $this->vendor->id)->first();
        $voucher = Voucher::create([
            'user_id' => $this->vendor->id,
            'code' => Voucher::generateCode('SC'),
            'package_id' => $package->id,
            'amount' => $package->price,
            'status' => 'available',
        ]);

        $response = $this->post("/portal/{$this->vendor->portal_slug}/connect", [
            'voucher_code' => $voucher->code,
            'mac_address' => 'AA:BB:CC:99:88:77',
            'device_name' => 'Guest Phone',
        ]);

        $response->assertStatus(200);
        $response->assertSee('You are Connected!');
        $this->assertEquals('active', $voucher->fresh()->status);
    }
}
