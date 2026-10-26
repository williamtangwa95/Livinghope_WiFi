<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print Voucher - {{ $voucher->code }}</title>
    <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
    <style>
        body {
            background: #f0f4f9;
            padding: 20px;
            font-family: system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }
        @media print {
            body {
                background: white;
                padding: 0;
            }
            .no-print {
                display: none !important;
            }
            .ticket-slip {
                box-shadow: none;
                border: 1px dashed black;
                margin: 0 auto;
            }
        }
    </style>
</head>
<body>
    @php
        $bizName = $host->business_name ?? 'LIVING HOPE WI-FI';
        $brandMark = $host->portal_logo_text ?? 'LH';
        $brandColor = $host->portal_theme_color ?? '#2563eb';
        $ssid = $host->portal_ssid ?? 'WIFI';
        $currency = $host->currency ?? 'TZS';
    @endphp

    <div class="no-print" style="max-width:340px;margin:10px auto;display:flex;justify-content:space-between;gap:10px;">
        <button class="btn btn-secondary" onclick="window.close()">← Close</button>
        <button class="btn btn-primary" onclick="window.print()">⎙ Print Ticket</button>
    </div>

    <div class="ticket-slip">
        <div class="brand-mark" style="background:{{ $brandColor }};margin:0 auto 10px;">{{ $brandMark }}</div>
        <div style="font-weight:900;letter-spacing:.08em;font-size:16px;">{{ strtoupper($bizName) }}</div>
        <div style="color:var(--muted);font-size:11px;margin-top:2px;">High Speed Internet Access Slip</div>

        <hr style="border:0;border-top:1px dashed #cfd9e8;margin:16px 0;">

        <div style="font-size:12px;color:var(--muted);">PACKAGE</div>
        <div style="font-size:18px;font-weight:850;margin:3px 0;">{{ $voucher->package->name ?? 'Wi-Fi Pass' }}</div>
        <div style="font-size:13px;font-weight:700;color:var(--blue);">{{ $currency }} {{ number_format($voucher->amount) }}</div>

        <div class="ticket-code">
            {{ $voucher->code }}
        </div>

        <div style="font-size:11px;color:var(--muted);line-height:1.6;text-align:left;background:#f8fafc;padding:12px;border-radius:10px;border:1px solid #edf2f7;">
            <div><strong>Connect to SSID:</strong> {{ $ssid }}</div>
            <div><strong>Speed Profile:</strong> {{ $voucher->package->speed_limit ?? '10 Mbps' }}</div>
            <div><strong>Max Devices:</strong> {{ $voucher->package->device_limit ?? 1 }}</div>
            <div><strong>Attendant / Cashier:</strong> {{ $voucher->cashier }}</div>
            <div><strong>Issued:</strong> {{ $voucher->created_at->format('d/m/Y H:i') }}</div>
            @if($host->phone)
                <div><strong>Help Contact:</strong> {{ $host->phone }}</div>
            @endif
        </div>

        <div style="font-size:10px;color:#8ea2c2;margin-top:16px;line-height:1.5;">
            Connect to <strong>{{ $ssid }}</strong> on your phone or laptop. Open your browser to the captive portal, and enter your voucher code.<br>
            Thank you for choosing {{ $bizName }}!
        </div>
    </div>
</body>
</html>
