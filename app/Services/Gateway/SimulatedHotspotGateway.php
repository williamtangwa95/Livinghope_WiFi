<?php

namespace App\Services\Gateway;

use App\Contracts\HotspotGatewayInterface;
use App\Models\ActiveSession;
use App\Models\Activity;
use App\Models\Voucher;
use App\Models\VoucherDevice;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class SimulatedHotspotGateway implements HotspotGatewayInterface
{
    /**
     * Authenticate and authorize voucher code in an atomic transaction.
     */
    public function authenticate(string $code, array $deviceContext): GatewayResult
    {
        if (! $this->isAvailable()) {
            return GatewayResult::failure(
                'Simulated Hotspot Gateway is currently unreachable or in failure test mode.',
                'GATEWAY_UNAVAILABLE',
                ['driver' => $this->getDriverName()]
            );
        }

        $code = strtoupper(trim($code));
        $macAddress = $this->normalizeMac($deviceContext['mac_address'] ?? null);
        $ipAddress = $deviceContext['ip_address'] ?? '192.168.0.105';
        $deviceName = $deviceContext['device_name'] ?? 'Simulated Client Device';
        $hostId = $deviceContext['host_id'] ?? null;

        return DB::transaction(function () use ($code, $macAddress, $ipAddress, $deviceName, $hostId) {
            // Lock voucher row for update to prevent concurrent duplicate activations
            $query = Voucher::with('package')->where('code', $code)->lockForUpdate();

            if ($hostId) {
                $query->where('user_id', $hostId);
            }

            $voucher = $query->first();

            if (! $voucher) {
                return GatewayResult::failure(
                    'Invalid voucher code. Please check your printed voucher ticket and try again.',
                    'VOUCHER_NOT_FOUND',
                    ['code' => $code]
                );
            }

            // Check if revoked
            if ($voucher->isRevoked()) {
                return GatewayResult::failure(
                    'This voucher has been revoked. Please contact the Wi-Fi attendant.',
                    'VOUCHER_REVOKED',
                    ['voucher' => $voucher]
                );
            }

            // Check if expired
            if ($voucher->isExpired()) {
                if ($voucher->status !== 'expired') {
                    $voucher->status = 'expired';
                    $voucher->save();
                }

                return GatewayResult::failure(
                    'This voucher has expired. Please purchase a new Wi-Fi package.',
                    'VOUCHER_EXPIRED',
                    ['voucher' => $voucher]
                );
            }

            // Check device limits on server
            if (! $this->checkDeviceLimit($voucher, $macAddress)) {
                $limit = $voucher->device_limit;

                return GatewayResult::failure(
                    "This voucher has reached its maximum device limit ({$limit} ".($limit === 1 ? 'device' : 'devices').'). Cannot connect additional devices.',
                    'DEVICE_LIMIT_EXCEEDED',
                    [
                        'voucher' => $voucher,
                        'device_limit' => $limit,
                        'current_device_count' => $voucher->devices()->count(),
                        'requested_mac' => $macAddress,
                    ]
                );
            }

            $now = Carbon::now();

            // If available, transition to active
            if ($voucher->isAvailable()) {
                $voucher->status = 'active';
                $voucher->activated_at = $now;
                $voucher->expires_at = $now->copy()->addHours($voucher->package->duration_hours);
                $voucher->mac_address = $macAddress;
                $voucher->save();

                Activity::create([
                    'user_id' => $voucher->user_id,
                    'icon' => '⌁',
                    'title' => 'Portal voucher activated (Simulated Gateway)',
                    'description' => "Voucher {$voucher->code} activated for {$voucher->package->name} [MAC: {$macAddress}]",
                    'type' => 'portal',
                ]);
            }

            // Register client device
            $voucherDevice = VoucherDevice::firstOrCreate(
                [
                    'voucher_id' => $voucher->id,
                    'mac_address' => $macAddress,
                ],
                [
                    'ip_address' => $ipAddress,
                    'device_name' => $deviceName,
                    'is_active' => true,
                    'first_authorized_at' => $now,
                    'last_seen_at' => $now,
                ]
            );

            $voucherDevice->update([
                'ip_address' => $ipAddress,
                'device_name' => $deviceName,
                'is_active' => true,
                'last_seen_at' => $now,
            ]);

            // Authorize session on simulated gateway
            return $this->authorizeSession($voucher, [
                'mac_address' => $macAddress,
                'ip_address' => $ipAddress,
                'device_name' => $deviceName,
                'voucher_device' => $voucherDevice,
            ]);
        });
    }

    /**
     * Authorize a session on the simulated gateway and record active session.
     */
    public function authorizeSession(Voucher $voucher, array $deviceContext): GatewayResult
    {
        $macAddress = $deviceContext['mac_address'];
        $ipAddress = $deviceContext['ip_address'] ?? '192.168.0.105';
        $deviceName = $deviceContext['device_name'] ?? 'Simulated Client';
        $now = Carbon::now();

        // Create or refresh ActiveSession record
        $session = ActiveSession::where('voucher_id', $voucher->id)
            ->where('mac_address', $macAddress)
            ->first();

        if (! $session) {
            $session = ActiveSession::create([
                'voucher_id' => $voucher->id,
                'ip_address' => $ipAddress,
                'mac_address' => $macAddress,
                'device_name' => $deviceName,
                'bytes_down' => rand(25, 75) * 1024 * 1024, // Simulated initial packets
                'bytes_up' => rand(5, 15) * 1024 * 1024,
                'connected_at' => $now,
                'status' => 'online',
            ]);
        } else {
            $session->update([
                'ip_address' => $ipAddress,
                'device_name' => $deviceName,
                'status' => 'online',
            ]);
        }

        $sessionMetadata = [
            'voucher' => $voucher,
            'session' => $session,
            'simulated_lease_id' => 'SIM-LEASE-'.strtoupper(substr(md5($macAddress.$now->timestamp), 0, 10)),
            'speed_limit' => $voucher->package->speed_limit,
            'device_limit' => $voucher->device_limit,
            'registered_devices' => $voucher->devices()->count(),
            'simulated' => true,
            'driver' => $this->getDriverName(),
            'disclaimer' => config('hotspot.disclaimer'),
        ];

        return GatewayResult::success(
            'Simulated gateway lease successfully authorized for device '.$macAddress,
            $sessionMetadata
        );
    }

    /**
     * Check device registration limit on the server.
     */
    public function checkDeviceLimit(Voucher $voucher, string $macAddress): bool
    {
        $normalizedMac = $this->normalizeMac($macAddress);

        // If this specific MAC address is already registered to this voucher, allow reconnection
        $alreadyRegistered = $voucher->devices()
            ->where('mac_address', $normalizedMac)
            ->exists();

        if ($alreadyRegistered) {
            return true;
        }

        // Otherwise check if new device exceeds device_limit
        $currentCount = $voucher->devices()->count();

        return $currentCount < $voucher->device_limit;
    }

    /**
     * Disconnect a customer session from the gateway.
     */
    public function disconnectSession(ActiveSession $session): bool
    {
        $session->status = 'terminated';
        $session->save();

        // Deactivate corresponding voucher device record
        VoucherDevice::where('voucher_id', $session->voucher_id)
            ->where('mac_address', $session->mac_address)
            ->update(['is_active' => false]);

        $code = $session->voucher?->code ?? 'N/A';
        Activity::create([
            'user_id' => $session->voucher?->user_id,
            'icon' => '✕',
            'title' => 'Simulated gateway session disconnected',
            'description' => "Terminated simulated lease for IP {$session->ip_address} [MAC: {$session->mac_address}, Voucher: {$code}]",
            'type' => 'session',
        ]);

        return true;
    }

    /**
     * Return session status and simulated gateway lease info.
     */
    public function getSessionStatus(ActiveSession $session): array
    {
        $voucher = $session->voucher;
        $isExpired = $voucher ? $voucher->isExpired() : false;

        return [
            'session_id' => $session->id,
            'status' => $isExpired ? 'expired' : $session->status,
            'ip_address' => $session->ip_address,
            'mac_address' => $session->mac_address,
            'device_name' => $session->device_name,
            'bytes_down' => $session->bytes_down,
            'bytes_up' => $session->bytes_up,
            'connected_at' => $session->connected_at?->toIso8601String(),
            'expires_at' => $voucher?->expires_at?->toIso8601String(),
            'is_expired' => $isExpired,
            'simulated' => true,
            'driver' => $this->getDriverName(),
        ];
    }

    /**
     * Clean up and expire stale sessions.
     */
    public function syncExpiredSessions(): int
    {
        $now = Carbon::now();
        $expiredCount = 0;

        // 1. Expire vouchers past expires_at
        $expiredVouchers = Voucher::where('status', 'active')
            ->whereNotNull('expires_at')
            ->where('expires_at', '<=', $now)
            ->get();

        foreach ($expiredVouchers as $voucher) {
            $voucher->status = 'expired';
            $voucher->save();

            // Terminate associated active sessions
            ActiveSession::where('voucher_id', $voucher->id)
                ->where('status', 'online')
                ->update(['status' => 'terminated']);

            VoucherDevice::where('voucher_id', $voucher->id)
                ->update(['is_active' => false]);

            $expiredCount++;
        }

        return $expiredCount;
    }

    /**
     * Check if gateway is available.
     */
    public function isAvailable(): bool
    {
        return ! config('hotspot.simulate_failure', false);
    }

    /**
     * Driver name.
     */
    public function getDriverName(): string
    {
        return 'simulated';
    }

    /**
     * Normalize MAC address format to XX:XX:XX:XX:XX:XX.
     */
    protected function normalizeMac(?string $mac): string
    {
        if (empty($mac)) {
            // Generate deterministic mock MAC based on request IP or microtime
            $randomHex = strtoupper(substr(md5(microtime()), 0, 12));

            return implode(':', str_split($randomHex, 2));
        }

        $clean = strtoupper(preg_replace('/[^A-Fa-f0-9]/', '', $mac));
        if (strlen($clean) === 12) {
            return implode(':', str_split($clean, 2));
        }

        return strtoupper(trim($mac));
    }
}
