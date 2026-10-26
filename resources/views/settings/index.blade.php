@extends('layouts.app')

@section('title', 'Hotspot & Captive Portal Settings | LIVING HOPE Wi-Fi')
@section('eyebrow', 'CONFIGURATION & BRANDING')
@section('page_title', 'Hotspot & Captive Portal Settings')

@section('content')
<div class="content">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;flex-wrap:wrap;gap:12px;">
        <div>
            <h2 style="margin:0 0 4px 0;font-size:20px;font-weight:700;color:var(--navy);">Captive Portal & Hotspot Ownership</h2>
            <p style="margin:0;font-size:13px;color:var(--muted);">Customize your customer login portal, brand colors, SSID, and Wi-Fi hardware configuration.</p>
        </div>

        <div style="display:flex;gap:10px;">
            <a href="{{ $user->portal_url }}" target="_blank" class="btn btn-primary" style="display:inline-flex;align-items:center;gap:6px;">
                <span>↗</span> Preview My Live Captive Portal
            </a>
        </div>
    </div>

    <!-- Portal Ownership Showcase Banner -->
    <div class="card" style="background:linear-gradient(135deg, #0f172a 0%, #1e293b 100%);color:white;padding:24px;margin-bottom:24px;border:1px solid rgba(255,255,255,0.1);">
        <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:16px;">
            <div>
                <span class="badge" style="background:rgba(56, 189, 248, 0.2);color:#38bdf8;border:1px solid rgba(56, 189, 248, 0.3);margin-bottom:8px;display:inline-block;">
                    YOUR DEDICATED CAPTIVE PORTAL
                </span>
                <h3 style="margin:0 0 6px 0;font-size:18px;color:#ffffff;">{{ $user->portal_title ?: $user->business_name }}</h3>
                <p style="margin:0;font-size:13px;color:#94a3b8;">
                    Broadcast SSID: <strong style="color:#ffffff;">{{ $user->portal_ssid }}</strong> · Public Portal URL:
                </p>
                <div style="display:flex;align-items:center;gap:10px;margin-top:10px;">
                    <input type="text" id="portalUrlInput" value="{{ $user->portal_url }}" readonly style="background:rgba(0,0,0,0.4);border:1px solid rgba(255,255,255,0.2);color:#38bdf8;padding:8px 12px;border-radius:8px;font-family:monospace;font-size:13px;min-width:320px;">
                    <button type="button" class="btn btn-secondary btn-sm" onclick="copyPortalUrl()" style="background:#ffffff20;border-color:#ffffff30;color:white;">
                        Copy URL
                    </button>
                </div>
            </div>

            <div style="text-align:right;">
                <div style="display:inline-flex;align-items:center;justify-content:center;width:70px;height:70px;border-radius:18px;background:{{ $user->portal_theme_color }};color:white;font-size:28px;font-weight:800;box-shadow:0 8px 20px rgba(0,0,0,0.3);">
                    {{ $user->portal_logo_text ?: 'LH' }}
                </div>
                <div style="margin-top:6px;font-size:11px;color:#94a3b8;">Portal Brand Mark</div>
            </div>
        </div>
    </div>

    <form action="{{ route('settings.update') }}" method="POST">
        @csrf
        <div class="grid grid-2">
            <!-- Business & Hotspot Profile -->
            <div class="card" style="padding:24px;">
                <h3 style="margin:0 0 16px 0;font-size:16px;color:var(--navy);border-bottom:1px solid var(--line);padding-bottom:10px;">
                    1. Hotspot Profile & Network
                </h3>

                <div class="form-group">
                    <label for="businessName">Business / Venue Name *</label>
                    <input type="text" id="businessName" name="business_name" value="{{ old('business_name', $user->business_name) }}" class="field" required>
                    <small style="color:var(--muted);font-size:11px;">Displayed on customer voucher slips and portal header.</small>
                </div>

                <div class="form-group">
                    <label for="portalSsid">Broadcast Wi-Fi SSID *</label>
                    <input type="text" id="portalSsid" name="portal_ssid" value="{{ old('portal_ssid', $user->portal_ssid) }}" class="field" placeholder="e.g. LIVING_HOPE_WIFI" required>
                    <small style="color:var(--muted);font-size:11px;">The Wi-Fi network name customers search for on their phones.</small>
                </div>

                <div class="form-row" style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                    <div class="form-group">
                        <label for="portalPhone">Support Contact Phone</label>
                        <input type="text" id="portalPhone" name="phone" value="{{ old('phone', $user->phone) }}" class="field" placeholder="+255 712 345 678">
                    </div>

                    <div class="form-group">
                        <label for="currencySelect">Currency Code *</label>
                        <select id="currencySelect" name="currency" class="field" required>
                            <option value="TZS" {{ $user->currency === 'TZS' ? 'selected' : '' }}>TZS (Tanzanian Shilling)</option>
                            <option value="KES" {{ $user->currency === 'KES' ? 'selected' : '' }}>KES (Kenyan Shilling)</option>
                            <option value="UGX" {{ $user->currency === 'UGX' ? 'selected' : '' }}>UGX (Ugandan Shilling)</option>
                            <option value="USD" {{ $user->currency === 'USD' ? 'selected' : '' }}>USD (US Dollar)</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label for="portalSlug">Portal URL Slug *</label>
                    <div style="display:flex;align-items:center;gap:6px;">
                        <span style="font-size:12px;color:var(--muted);white-space:nowrap;">{{ url('/portal') }}/</span>
                        <input type="text" id="portalSlug" name="portal_slug" value="{{ old('portal_slug', $user->portal_slug) }}" class="field" required style="font-family:monospace;">
                    </div>
                    <small style="color:var(--muted);font-size:11px;">Must be unique. Changing this changes your public captive portal web link.</small>
                </div>
            </div>

            <!-- Captive Portal Customizer -->
            <div class="card" style="padding:24px;">
                <h3 style="margin:0 0 16px 0;font-size:16px;color:var(--navy);border-bottom:1px solid var(--line);padding-bottom:10px;">
                    2. Captive Portal Branding
                </h3>

                <div class="form-group">
                    <label for="portalTitle">Portal Headline Title *</label>
                    <input type="text" id="portalTitle" name="portal_title" value="{{ old('portal_title', $user->portal_title) }}" class="field" required>
                </div>

                <div class="form-group">
                    <label for="portalWelcome">Welcome Greeting Message</label>
                    <textarea id="portalWelcome" name="portal_welcome_text" class="field" rows="3">{{ old('portal_welcome_text', $user->portal_welcome_text) }}</textarea>
                    <small style="color:var(--muted);font-size:11px;">Shown above the voucher code entry box on your captive portal.</small>
                </div>

                <div class="form-row" style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                    <div class="form-group">
                        <label for="portalLogo">Brand Mark (1-4 Chars) *</label>
                        <input type="text" id="portalLogo" name="portal_logo_text" value="{{ old('portal_logo_text', $user->portal_logo_text) }}" class="field" maxlength="4" required>
                    </div>

                    <div class="form-group">
                        <label for="portalColor">Primary Theme Color *</label>
                        <div style="display:flex;gap:8px;align-items:center;">
                            <input type="color" id="portalColorPicker" value="{{ $user->portal_theme_color ?: '#2563eb' }}" onchange="document.getElementById('portalColor').value = this.value;" style="width:40px;height:40px;padding:2px;border-radius:8px;border:1px solid var(--line);cursor:pointer;">
                            <input type="text" id="portalColor" name="portal_theme_color" value="{{ old('portal_theme_color', $user->portal_theme_color ?: '#2563eb') }}" class="field" style="flex:1;" required>
                        </div>
                    </div>
                </div>

                <div style="margin-top:14px;background:#f8fafc;border:1px solid var(--line);border-radius:12px;padding:12px;">
                    <span style="font-size:11px;font-weight:700;color:var(--muted);text-transform:uppercase;">Color Swatch Presets:</span>
                    <div style="display:flex;gap:8px;margin-top:8px;">
                        <button type="button" onclick="setColor('#2563eb')" style="width:24px;height:24px;border-radius:6px;background:#2563eb;border:none;cursor:pointer;" title="Royal Blue"></button>
                        <button type="button" onclick="setColor('#059669')" style="width:24px;height:24px;border-radius:6px;background:#059669;border:none;cursor:pointer;" title="Emerald Green"></button>
                        <button type="button" onclick="setColor('#d97706')" style="width:24px;height:24px;border-radius:6px;background:#d97706;border:none;cursor:pointer;" title="Amber Gold"></button>
                        <button type="button" onclick="setColor('#7c3aed')" style="width:24px;height:24px;border-radius:6px;background:#7c3aed;border:none;cursor:pointer;" title="Purple"></button>
                        <button type="button" onclick="setColor('#e11d48')" style="width:24px;height:24px;border-radius:6px;background:#e11d48;border:none;cursor:pointer;" title="Crimson"></button>
                    </div>
                </div>

                <div style="margin-top:20px;text-align:right;">
                    <button type="submit" class="btn btn-primary" style="padding:12px 28px;">
                        Save Hotspot & Portal Settings
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
    function copyPortalUrl() {
        const input = document.getElementById('portalUrlInput');
        input.select();
        document.execCommand('copy');
        showToast('Captive portal URL copied to clipboard: ' + input.value);
    }

    function setColor(hex) {
        document.getElementById('portalColor').value = hex;
        document.getElementById('portalColorPicker').value = hex;
    }
</script>
@endsection
