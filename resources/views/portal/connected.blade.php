<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="{{ $host->portal_theme_color ?? '#10b981' }}">
    <title>Connected to Wi-Fi | {{ $host->business_name ?? 'Wi-Fi Hotspot' }}</title>
    <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
    <style>
        body {
            background: #f4f7fb;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            font-family: system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }
        .connected-card {
            background: white;
            border-radius: 24px;
            padding: 36px 30px;
            max-width: 480px;
            width: 100%;
            text-align: center;
            box-shadow: 0 16px 40px rgba(0,0,0,0.06);
            border: 1px solid #e2e8f0;
        }
        .success-icon {
            width: 64px;
            height: 64px;
            border-radius: 50%;
            background: #e5f8ef;
            color: #12835b;
            display: grid;
            place-items: center;
            font-size: 28px;
            margin: 0 auto 18px;
            box-shadow: 0 8px 20px rgba(18, 131, 91, 0.15);
        }
        .gateway-tag {
            background: #f1f5f9;
            border: 1px solid #cbd5e1;
            padding: 4px 10px;
            border-radius: 6px;
            font-size: 11px;
            color: #475569;
            display: inline-block;
            margin-top: 14px;
        }
    </style>
</head>
<body>
    <div class="connected-card">
        <div class="success-icon">✓</div>
        <div class="eyebrow" style="color:var(--green);">CONNECTION ESTABLISHED</div>
        <h2 style="font-size:24px;letter-spacing:-.04em;margin:10px 0 6px;">You are Connected!</h2>
        <p style="color:var(--muted);font-size:13px;line-height:1.6;margin:0 0 20px;">
            Your device is now authenticated on <strong>{{ $host->business_name ?? 'the Wi-Fi Hotspot' }}</strong>.
        </p>

        <div style="background:#f8fafc;padding:16px;border-radius:14px;text-align:left;font-size:12px;line-height:1.8;color:#334155;margin-bottom:20px;border:1px solid #edf2f7;">
            <div><strong>Active Voucher:</strong> <span style="font-family:monospace;font-weight:700;">{{ $voucher->code }}</span></div>
            <div><strong>Package:</strong> {{ $voucher->package->name }} ({{ $voucher->package->duration_hours }} Hours)</div>
            <div><strong>Speed Profile:</strong> {{ $voucher->package->speed_limit }}</div>
            <div><strong>Device Limit:</strong> <span style="color:var(--primary);font-weight:700;">{{ $voucher->devices()->count() }} / {{ $voucher->device_limit }} {{ $voucher->device_limit === 1 ? 'Device' : 'Devices' }} registered</span></div>
            @if($session)
                <div><strong>Client Device:</strong> <code>{{ $session->mac_address }}</code> ({{ $session->device_name }})</div>
                <div><strong>Client IP:</strong> <code>{{ $session->ip_address }}</code></div>
            @endif
            <div><strong>Expires At:</strong> {{ $voucher->expires_at ? $voucher->expires_at->format('M d, Y · H:i') : 'Active' }}</div>
            <div><strong>Time Left:</strong> <span style="color:var(--green);font-weight:750;">{{ $voucher->expires_at ? $voucher->expires_at->diffForHumans(['parts' => 2]) : 'Active' }}</span></div>
            <div><strong>Network SSID:</strong> {{ $host->portal_ssid ?? 'WIFI' }}</div>
        </div>

        @if(!empty($gatewayData['simulated']))
            <div class="gateway-tag">
                ⚙ <strong>Simulated Gateway Lease Active:</strong> <code>{{ $gatewayData['simulated_lease_id'] ?? 'SIM-LEASE' }}</code>
                <br>
                <small style="color:#64748b;">Notice: Simulated Gateway active for testing. Does not route real traffic.</small>
            </div>
        @endif

        <div style="display:flex;flex-direction:column;gap:10px;margin-top:20px;">
            <a href="https://www.google.com" target="_blank" class="btn btn-primary" style="padding:13px;font-size:13px;background:{{ $host->portal_theme_color ?? 'var(--primary)' }};">
                Start Browsing Internet ➔
            </a>
            <a href="{{ $host ? route('portal.show', $host->portal_slug ?? $host->id) : route('portal.index') }}" class="btn btn-secondary" style="font-size:12px;">
                Return to Captive Portal
            </a>
        </div>
    </div>
</body>
</html>
