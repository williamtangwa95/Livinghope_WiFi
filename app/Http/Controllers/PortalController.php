<?php

namespace App\Http\Controllers;

use App\Contracts\HotspotGatewayInterface;
use App\Models\Package;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PortalController extends Controller
{
    public function __construct(
        protected HotspotGatewayInterface $gateway
    ) {}

    /**
     * Customer Captive Portal Landing Page for a specific hotspot owner.
     */
    public function show(Request $request, string $slug)
    {
        $host = User::where('portal_slug', $slug)->orWhere('id', $slug)->first();

        if (! $host) {
            abort(404, 'Wi-Fi Hotspot Captive Portal not found.');
        }

        $packages = Package::where('user_id', $host->id)->where('is_active', true)->get();

        // Simulated Client Device Context for local testing
        $simulatedMac = $request->query('sim_mac', $request->cookie('sim_mac', $this->resolveClientMac($request)));
        $simulatedDeviceName = $request->query('sim_device', $this->resolveDeviceName($request));

        $gatewayInfo = [
            'driver' => $this->gateway->getDriverName(),
            'is_available' => $this->gateway->isAvailable(),
            'mock_mode' => (bool) config('hotspot.mock_mode', true),
            'disclaimer' => config('hotspot.disclaimer'),
        ];

        return response()
            ->view('portal.index', compact('host', 'packages', 'gatewayInfo', 'simulatedMac', 'simulatedDeviceName'))
            ->cookie('sim_mac', $simulatedMac, 120);
    }

    /**
     * Default portal route (redirects to owner's portal or default admin portal).
     */
    public function index()
    {
        if (Auth::check()) {
            $user = Auth::user()->getEffectiveUser();

            return redirect()->route('portal.show', $user->portal_slug ?: $user->id);
        }

        // Default to first admin or first available hotspot
        $defaultHost = User::where('role', 'admin')->first() ?: User::first();
        if ($defaultHost) {
            return redirect()->route('portal.show', $defaultHost->portal_slug ?: $defaultHost->id);
        }

        abort(404, 'No active Wi-Fi portals found.');
    }

    /**
     * Connect/Login via Voucher Code on Captive Portal through the Gateway.
     */
    public function connect(Request $request, ?string $slug = null)
    {
        $validated = $request->validate([
            'voucher_code' => 'required|string|max:50',
            'mac_address' => 'nullable|string|max:30',
            'device_name' => 'nullable|string|max:100',
            'host_id' => 'nullable|integer',
        ]);

        $code = strtoupper(trim($validated['voucher_code']));

        // Find the host either by slug or host_id
        $host = null;
        if ($slug) {
            $host = User::where('portal_slug', $slug)->orWhere('id', $slug)->first();
        } elseif ($request->filled('host_id')) {
            $host = User::find($request->input('host_id'));
        }

        // Resolve client device identity (supports simulated MAC in local mock mode)
        $mac = $request->input('mac_address') ?: $request->cookie('sim_mac', $this->resolveClientMac($request));
        $ip = $request->ip() ?: '192.168.0.105';
        $deviceName = $request->input('device_name') ?: $this->resolveDeviceName($request);

        $deviceContext = [
            'mac_address' => $mac,
            'ip_address' => $ip,
            'device_name' => $deviceName,
            'host_id' => $host?->id,
        ];

        // Process voucher authentication and authorization through the gateway
        $result = $this->gateway->authenticate($code, $deviceContext);

        if (! $result->isSuccess()) {
            return back()
                ->with('error', $result->message)
                ->with('error_code', $result->errorCode)
                ->withInput();
        }

        $voucher = $result->getVoucher();
        $session = $result->getSession();
        $gatewayData = $result->data;

        return view('portal.connected', compact('voucher', 'host', 'session', 'gatewayData'));
    }

    /**
     * Deterministically derive or generate a simulated client MAC address.
     */
    protected function resolveClientMac(Request $request): string
    {
        // Deterministic hash based on client IP & User-Agent for consistent testing
        $seed = ($request->ip() ?? '127.0.0.1').($request->header('User-Agent') ?? 'DefaultClient');
        $hash = strtoupper(substr(md5($seed), 0, 12));

        return implode(':', str_split($hash, 2));
    }

    /**
     * Resolve readable client device name.
     */
    protected function resolveDeviceName(Request $request): string
    {
        $ua = $request->header('User-Agent', '');
        if (str_contains($ua, 'iPhone')) {
            return 'Apple iPhone';
        }
        if (str_contains($ua, 'Android')) {
            return 'Android Mobile';
        }
        if (str_contains($ua, 'Macintosh')) {
            return 'MacBook Laptop';
        }
        if (str_contains($ua, 'Windows')) {
            return 'Windows PC';
        }

        return 'Customer Device';
    }
}
