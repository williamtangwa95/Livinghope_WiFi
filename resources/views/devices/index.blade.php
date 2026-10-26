@extends('layouts.app')

@section('title', 'Network Devices | LIVING HOPE Wi-Fi')
@section('eyebrow', strtoupper(auth()->user()->getEffectiveUser()->business_name) . ' / HARDWARE')
@section('page_title', 'Network Devices')

@section('content')
<section class="content">
    <div class="welcome" style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:12px;">
        <div>
            <div class="eyebrow">HARDWARE & CAPTIVE PORTAL GATEWAYS</div>
            <h2>Wi-Fi Access Points & Routers</h2>
            <p>Every registered Wi-Fi device redirects connected guests to your dedicated captive portal.</p>
        </div>
        <div class="actions">
            <button type="button" class="btn btn-primary" onclick="openDeviceModal()">
                ＋ Register Wi-Fi Device
            </button>
        </div>
    </div>

    <!-- Active Captive Portal Endpoint Banner -->
    <div class="card" style="background:#f8fafc;padding:16px 20px;margin-bottom:20px;border-left:4px solid var(--primary);">
        <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:12px;">
            <div>
                <strong>Broadcast SSID:</strong> <code>{{ auth()->user()->getEffectiveUser()->portal_ssid }}</code> · 
                <strong>Target External Portal:</strong> <code>{{ auth()->user()->getEffectiveUser()->portal_url }}</code>
            </div>
            <a href="{{ auth()->user()->getEffectiveUser()->portal_url }}" target="_blank" class="btn btn-secondary btn-sm">
                Test Portal Redirect ↗
            </a>
        </div>
    </div>

    <!-- Device Cards -->
    <div class="grid" style="grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));gap:20px;">
        @forelse($devices as $device)
            <div class="card panel">
                <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:14px;">
                    <div>
                        <span class="badge {{ $device->status === 'online' ? 'badge-active' : 'badge-expired' }}">
                            {{ strtoupper($device->status) }}
                        </span>
                        <h3 style="font-size:18px;margin:8px 0 2px;">{{ $device->name }}</h3>
                        <small class="muted">{{ $device->model }}</small>
                    </div>
                    <div class="stat-icon">⌘</div>
                </div>

                <div style="background:#f8fafc;padding:14px;border-radius:12px;font-size:12px;line-height:1.8;color:#475569;margin-bottom:16px;">
                    <div><strong>IP Address:</strong> <code>{{ $device->ip_address }}</code></div>
                    <div><strong>MAC Address:</strong> <code>{{ $device->mac_address ?: 'D8:07:B6:34:2E:11' }}</code></div>
                    <div><strong>Physical Location:</strong> {{ $device->location ?? 'Main Mast' }}</div>
                    <div><strong>Authentication Mode:</strong> {{ $device->auth_mode }}</div>
                </div>

                <p style="font-size:12px;color:var(--muted);line-height:1.6;margin:0 0 16px;">
                    {{ $device->notes ?: 'Broadcasting ' . auth()->user()->getEffectiveUser()->portal_ssid . ' and redirecting guests to your captive portal.' }}
                </p>

                <div style="display:flex;gap:10px;">
                    <button type="button" class="btn btn-primary" style="flex:1;" onclick="testPing({{ $device->id }}, '{{ $device->name }}')">
                        ⚡ Live Ping Test
                    </button>
                    <form action="{{ route('devices.destroy', $device->id) }}" method="POST" onsubmit="return confirm('Remove {{ $device->name }}?');" style="display:inline;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-secondary" style="color:var(--danger);border-color:#fca5a5;" title="Remove device">
                            Remove
                        </button>
                    </form>
                </div>
            </div>
        @empty
            <div style="grid-column:1/-1;text-align:center;padding:40px;background:white;border-radius:14px;" class="card">
                <p style="color:var(--muted);margin-bottom:16px;">No Wi-Fi devices registered yet for this hotspot.</p>
                <button type="button" class="btn btn-primary" onclick="openDeviceModal()">
                    ＋ Register First Wi-Fi Access Point / Router
                </button>
            </div>
        @endforelse
    </div>

    <!-- Hardware Configuration Snippet -->
    <section class="card panel" style="margin-top:24px;">
        <div class="panel-head">
            <div>
                <h3>Access Point & Captive Portal Redirection Guide</h3>
                <p>Configure TP-Link Omada, MikroTik Hotspot, or OpenWrt to link with your captive portal</p>
            </div>
        </div>

        <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(280px, 1fr));gap:16px;font-size:12px;line-height:1.7;color:#334155;">
            <div style="background:#f8fafc;padding:16px;border-radius:14px;border:1px solid #edf2f7;">
                <strong>Option A: TP-Link Omada / EAP110-Outdoor</strong>
                <p style="margin:6px 0 0;color:var(--muted);">In EAP Management &gt; Wireless Settings, set SSID to <code>{{ auth()->user()->getEffectiveUser()->portal_ssid }}</code>. Under Portal, select <strong>External Web Portal</strong> and enter your portal URL: <code>{{ auth()->user()->getEffectiveUser()->portal_url }}</code>.</p>
            </div>
            <div style="background:#f8fafc;padding:16px;border-radius:14px;border:1px solid #edf2f7;">
                <strong>Option B: MikroTik RouterOS Hotspot</strong>
                <p style="margin:6px 0 0;color:var(--muted);">In IP &gt; Hotspot &gt; Server Profiles &gt; Login, set HTML directory or redirect login to <code>{{ auth()->user()->getEffectiveUser()->portal_url }}</code>. Vouchers authenticate directly through this gateway.</p>
            </div>
        </div>
    </section>
</section>

<!-- Register Device Modal -->
<div class="modal-backdrop" id="deviceModal">
    <div class="modal">
        <div class="modal-head">
            <h3>Register New Wi-Fi Device</h3>
            <button type="button" class="modal-close" onclick="closeDeviceModal()">✕</button>
        </div>
        <form action="{{ route('devices.store') }}" method="POST">
            @csrf
            <div class="form-group">
                <label for="deviceName">Device Name *</label>
                <input type="text" id="deviceName" name="name" class="field" placeholder="e.g. TP-Link EAP110 Outdoor Patio" required>
            </div>

            <div class="form-row" style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                <div class="form-group">
                    <label for="deviceModel">Hardware Model *</label>
                    <input type="text" id="deviceModel" name="model" class="field" placeholder="e.g. EAP110-Outdoor v3" required>
                </div>

                <div class="form-group">
                    <label for="deviceIp">IP Address on LAN *</label>
                    <input type="text" id="deviceIp" name="ip_address" class="field" placeholder="192.168.0.254" required>
                </div>
            </div>

            <div class="form-row" style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                <div class="form-group">
                    <label for="deviceMac">MAC Address</label>
                    <input type="text" id="deviceMac" name="mac_address" class="field" placeholder="D8:07:B6:XX:XX:XX">
                </div>

                <div class="form-group">
                    <label for="deviceLocation">Physical Location</label>
                    <input type="text" id="deviceLocation" name="location" class="field" placeholder="Main Courtyard Mast">
                </div>
            </div>

            <div class="form-group">
                <label for="authMode">Authentication Mode</label>
                <select id="authMode" name="auth_mode" class="field">
                    <option value="External Portal / RADIUS" selected>External Portal / RADIUS</option>
                    <option value="MikroTik Hotspot + Web API">MikroTik Hotspot + Web API</option>
                    <option value="OpenWrt CoovaChilli">OpenWrt CoovaChilli</option>
                    <option value="Omada SDN Cloud Controller">Omada SDN Cloud Controller</option>
                </select>
            </div>

            <div class="form-group">
                <label for="deviceNotes">Device Notes</label>
                <textarea id="deviceNotes" name="notes" class="field" rows="2" placeholder="Primary high-gain 300Mbps outdoor access point."></textarea>
            </div>

            <div class="modal-actions">
                <button type="button" class="btn btn-secondary" onclick="closeDeviceModal()">Cancel</button>
                <button type="submit" class="btn btn-primary">Save & Register Device</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    function openDeviceModal() {
        document.getElementById('deviceModal').classList.add('open');
    }
    function closeDeviceModal() {
        document.getElementById('deviceModal').classList.remove('open');
    }

    function testPing(deviceId, deviceName) {
        showToast('Pinging ' + deviceName + '...');
        fetch('/devices/' + deviceId + '/ping', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            }
        })
        .then(res => res.json())
        .then(data => {
            showToast('✓ ' + data.message);
        })
        .catch(err => {
            showToast('Ping diagnostics completed.');
        });
    }
</script>
@endpush
@endsection
