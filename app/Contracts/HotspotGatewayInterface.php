<?php

namespace App\Contracts;

use App\Models\ActiveSession;
use App\Models\Voucher;
use App\Services\Gateway\GatewayResult;

interface HotspotGatewayInterface
{
    /**
     * Authenticate and validate a voucher code for a given device context.
     *
     * @param  array  $deviceContext  ['mac_address' => string, 'ip_address' => string, 'device_name' => ?string, 'host_id' => ?int]
     */
    public function authenticate(string $code, array $deviceContext): GatewayResult;

    /**
     * Authorize a customer session on the gateway after successful voucher validation.
     */
    public function authorizeSession(Voucher $voucher, array $deviceContext): GatewayResult;

    /**
     * Check if the device is permitted under the voucher package's device limits.
     */
    public function checkDeviceLimit(Voucher $voucher, string $macAddress): bool;

    /**
     * Disconnect a customer session from the gateway.
     */
    public function disconnectSession(ActiveSession $session): bool;

    /**
     * Retrieve status, traffic statistics, and lease information for a session.
     */
    public function getSessionStatus(ActiveSession $session): array;

    /**
     * Sweep and expire all sessions whose voucher duration or expiration has passed.
     *
     * @return int Number of sessions terminated
     */
    public function syncExpiredSessions(): int;

    /**
     * Check whether the gateway service is online and reachable.
     */
    public function isAvailable(): bool;

    /**
     * Get the gateway driver name.
     */
    public function getDriverName(): string;
}
