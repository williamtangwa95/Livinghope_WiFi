<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="{{ $host->portal_theme_color ?? '#2563eb' }}">
    <title>Connect to Internet | {{ $host->portal_title ?? ($host->business_name ?? 'Wi-Fi Hotspot') }}</title>
    <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
    <style>
        :root {
            --portal-accent: {{ $host->portal_theme_color ?? '#2563eb' }};
        }
        body {
            background: #f4f7fb;
            min-height: 100vh;
            font-family: system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            margin: 0;
            padding: 0;
        }
        .portal-nav {
            padding: 16px 28px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #ffffff;
            border-bottom: 1px solid var(--line);
            box-shadow: 0 2px 10px rgba(0,0,0,0.02);
        }
        .brand-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 44px;
            height: 44px;
            border-radius: 12px;
            background: var(--portal-accent);
            color: #ffffff;
            font-size: 18px;
            font-weight: 800;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }
        .customer-hero {
            background: linear-gradient(135deg, #0b1220 0%, #172554 100%);
            border-radius: 20px;
            padding: 36px 32px;
            color: #ffffff;
            margin-bottom: 30px;
            position: relative;
            overflow: hidden;
            border-top: 4px solid var(--portal-accent);
        }
        .ssid-tag {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: rgba(255, 255, 255, 0.12);
            border: 1px solid rgba(255, 255, 255, 0.2);
            padding: 6px 14px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 600;
            margin-bottom: 16px;
        }
        .portal-login-box {
            background: #ffffff;
            color: var(--navy);
            border-radius: 16px;
            padding: 24px;
            margin-top: 24px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
        }
        .btn-connect {
            background: var(--portal-accent);
            color: #ffffff;
            border: none;
            padding: 12px 24px;
            border-radius: 10px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        .btn-connect:hover {
            opacity: 0.92;
            transform: translateY(-1px);
        }
        .customer-package.featured {
            border-color: var(--portal-accent);
            box-shadow: 0 10px 25px rgba(37, 99, 235, 0.12);
        }
        .customer-package .package-label {
            background: var(--portal-accent);
            color: #ffffff;
        }
        .gateway-banner {
            background: rgba(15, 23, 42, 0.9);
            border: 1px solid rgba(56, 189, 248, 0.3);
            border-radius: 14px;
            padding: 14px 18px;
            margin-bottom: 20px;
            color: #cbd5e1;
            font-size: 12px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 12px;
        }
        .device-switcher-chip {
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: #f1f5f9;
            padding: 4px 10px;
            border-radius: 6px;
            font-size: 11px;
            cursor: pointer;
            text-decoration: none;
        }
        .device-switcher-chip:hover {
            background: var(--portal-accent);
            border-color: var(--portal-accent);
        }
    </style>
</head>
<body>
    <header class="portal-nav">
        <div style="display:flex;align-items:center;gap:12px;">
            <div class="brand-badge">{{ $host->portal_logo_text ?? 'LH' }}</div>
            <div>
                <div style="font-weight:800;color:var(--navy);font-size:16px;">{{ $host->business_name ?? 'LIVING HOPE WI-FI' }}</div>
                <div style="font-size:12px;color:var(--muted);">Captive Portal · Hotspot Gateway</div>
            </div>
        </div>
        <div>
            @auth
                <a href="{{ route('dashboard') }}" class="btn btn-secondary btn-sm">
                    Dashboard ↗
                </a>
            @else
                <a href="{{ route('login') }}" class="btn btn-secondary btn-sm">
                    Hotspot Admin Login ↗
                </a>
            @endauth
        </div>
    </header>

    <div class="customer-page" style="display:block;margin-top:20px;max-width:980px;margin-left:auto;margin-right:auto;padding:0 16px;">
        <!-- Simulated HotSpot Gateway Developer Testing Banner -->
        @if($gatewayInfo['mock_mode'] ?? true)
            <div class="gateway-banner">
                <div>
                    <span style="color:#38bdf8;font-weight:700;">⚙ SIMULATED GATEWAY ACTIVE</span> · 
                    <span>Driver: <code>{{ $gatewayInfo['driver'] }}</code></span> · 
                    <span>Simulated Client MAC: <code id="displayMac">{{ $simulatedMac }}</code></span>
                    <div style="font-size:11px;color:#94a3b8;margin-top:2px;">
                        {{ $gatewayInfo['disclaimer'] }}
                    </div>
                </div>
                <div style="display:flex;gap:6px;align-items:center;flex-wrap:wrap;">
                    <span style="font-size:11px;color:#94a3b8;">Test Device Limit:</span>
                    <a href="?sim_mac=AA:BB:CC:11:22:01&sim_device=Phone-A" class="device-switcher-chip" title="Simulate Device A">Device A (Phone)</a>
                    <a href="?sim_mac=AA:BB:CC:11:22:02&sim_device=Laptop-B" class="device-switcher-chip" title="Simulate Device B">Device B (Laptop)</a>
                    <a href="?sim_mac=AA:BB:CC:11:22:03&sim_device=Tablet-C" class="device-switcher-chip" title="Simulate Device C">Device C (Tablet)</a>
                </div>
            </div>
        @endif

        @if(session('error'))
            <div style="background:#ffebee;color:#bd3f4d;border:1px solid #fecdd3;padding:14px 18px;border-radius:14px;margin-bottom:16px;font-size:13px;display:flex;align-items:center;gap:10px;">
                <span>⚠️</span>
                <div>
                    <strong>Access Denied:</strong> {{ session('error') }}
                    @if(session('error_code'))
                        <div style="font-size:11px;opacity:0.8;margin-top:2px;">Error Code: <code>{{ session('error_code') }}</code></div>
                    @endif
                </div>
            </div>
        @endif

        @if(session('success'))
            <div style="background:#e5f8ef;color:#12835b;border:1px solid #bbf7d0;padding:14px 18px;border-radius:14px;margin-bottom:16px;font-size:13px;display:flex;align-items:center;gap:10px;">
                <span>✓</span>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        <div class="customer-hero">
            <span class="ssid-tag">
                <span>📶</span> Connected to: <strong>{{ $host->portal_ssid ?? 'WIFI_NETWORK' }}</strong>
            </span>
            <h2 style="font-size:28px;margin:8px 0 12px 0;">{{ $host->portal_title ?? ($host->business_name . ' Public Wi-Fi') }}</h2>
            <p style="color:#cbd5e1;line-height:1.6;max-width:650px;margin:0 0 16px 0;">
                {{ $host->portal_welcome_text ?? 'Stay connected for study, work, browsing and social media. Enter your purchased voucher code below to activate your internet access.' }}
            </p>
            
            <!-- Voucher Login Box -->
            <div class="portal-login-box">
                <h3 style="margin:0 0 4px 0;font-size:17px;font-weight:700;">Enter Voucher Access Code</h3>
                <p style="margin:0 0 16px 0;font-size:13px;color:var(--muted);">
                    Type the code printed on your Wi-Fi voucher slip:
                </p>
                <form action="{{ route('portal.connect.slug', $host->portal_slug ?? $host->id) }}" method="POST" style="display:flex;flex-direction:column;gap:12px;">
                    @csrf
                    <input type="hidden" name="host_id" value="{{ $host->id }}">
                    <input type="hidden" name="mac_address" id="formMac" value="{{ $simulatedMac }}">
                    <input type="hidden" name="device_name" id="formDevice" value="{{ $simulatedDeviceName }}">

                    <div style="display:flex;gap:10px;flex-wrap:wrap;">
                        <input type="text" name="voucher_code" class="field portal-code-input" placeholder="e.g. {{ $host->portal_logo_text ?? 'LH' }}-XXXX-XXXX" value="{{ old('voucher_code') }}" style="flex:1;min-width:220px;font-family:monospace;font-size:16px;font-weight:700;letter-spacing:1px;text-transform:uppercase;" required autofocus>
                        <button type="submit" class="btn-connect">
                            Connect to Internet ➔
                        </button>
                    </div>

                    <div style="font-size:11px;color:var(--muted);display:flex;justify-content:space-between;align-items:center;">
                        <span>Simulated MAC: <code>{{ $simulatedMac }}</code> ({{ $simulatedDeviceName }})</span>
                        <a href="javascript:void(0)" onclick="promptCustomMac()" style="color:var(--primary);text-decoration:none;">Custom MAC Address</a>
                    </div>
                </form>
            </div>

            <div style="display:flex;justify-content:space-between;align-items:center;margin-top:16px;font-size:12px;color:#94a3b8;flex-wrap:wrap;gap:8px;">
                <a href="#packagesList" style="color:#38bdf8;text-decoration:none;">View available internet packages ↓</a>
                @if($host->phone)
                    <span>Attendant Support: <strong style="color:#ffffff;">{{ $host->phone }}</strong></span>
                @endif
            </div>
        </div>

        <div class="customer-section" id="packagesList">
            <div class="customer-section-head">
                <div>
                    <h3 style="font-size:20px;margin:0 0 4px 0;">Wi-Fi Packages & Pricing</h3>
                    <p style="margin:0;color:var(--muted);font-size:13px;">Choose your duration. Purchase your voucher from the reception or cashier counter.</p>
                </div>
                <span class="eyebrow">{{ $host->currency ?? 'TZS' }} RATES</span>
            </div>

            <div class="grid customer-packages" style="margin-top:20px;">
                @forelse($packages as $pkg)
                    <article class="card customer-package {{ $pkg->badge_label === 'POPULAR CHOICE' || $pkg->badge_label === 'MOST POPULAR' ? 'featured' : '' }}">
                        <span class="package-label">{{ $pkg->badge_label }}</span>
                        <div style="font-size:24px;margin-bottom:8px;">{{ $pkg->icon ?: '◈' }}</div>
                        <h4 style="margin:0 0 8px 0;font-size:17px;">{{ $pkg->name }}</h4>
                        <div class="customer-price">
                            {{ number_format($pkg->price) }} <small>{{ $host->currency ?? 'TZS' }}</small>
                        </div>
                        <p style="font-size:12px;color:var(--muted);margin:8px 0 16px 0;">{{ $pkg->description }}</p>
                        <div style="font-size:11px;color:var(--muted);margin-bottom:14px;border-top:1px dashed var(--line);padding-top:8px;">
                            <span>⚡ Speed: <strong>{{ $pkg->speed_limit }}</strong></span> · 
                            <span>📱 Concurrency: <strong>{{ $pkg->device_limit }} {{ $pkg->device_limit === 1 ? 'Device' : 'Devices' }}</strong></span>
                        </div>
                        <button type="button" class="btn btn-secondary" onclick="alert('To purchase the {{ $pkg->name }} package ({{ $pkg->formatted_price }}), please ask the counter cashier: {{ $host->name }} or call {{ $host->phone ?? 'reception' }}.')">
                            How to Buy
                        </button>
                    </article>
                @empty
                    <div style="grid-column:1/-1;text-align:center;padding:40px;background:white;border-radius:14px;">
                        <p style="color:var(--muted);">No packages currently published for this hotspot.</p>
                    </div>
                @endforelse
            </div>
        </div>

        <footer style="margin:40px 0;text-align:center;font-size:12px;color:var(--muted);border-top:1px solid var(--line);padding-top:20px;">
            <div>© {{ date('Y') }} {{ $host->business_name }} · Powered by Cloud Hotspot Gateway</div>
            <div style="margin-top:4px;">SSID: {{ $host->portal_ssid }} · High-Speed Internet Access</div>
        </footer>
    </div>

    <script>
        function promptCustomMac() {
            const current = document.getElementById('formMac').value;
            const newMac = prompt('Enter a simulated MAC address for testing device-limits (e.g. AA:BB:CC:11:22:33):', current);
            if (newMac && newMac.trim()) {
                window.location.href = '?sim_mac=' + encodeURIComponent(newMac.trim()) + '&sim_device=Custom-Device';
            }
        }
    </script>
</body>
</html>
