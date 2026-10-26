@extends('layouts.app')

@section('title', 'Active Sessions | LIVING HOPE Wi-Fi')
@section('eyebrow', strtoupper(auth()->user()->getEffectiveUser()->business_name) . ' / SESSIONS')
@section('page_title', 'Active Sessions')

@section('content')
<section class="content">
    <div class="welcome" style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:12px;">
        <div>
            <div class="eyebrow">REAL-TIME GATEWAY LEASES & SESSIONS</div>
            <h2>Active Sessions</h2>
            <p>Monitor connected client MACs, assigned IP addresses, data consumption and gateway status.</p>
        </div>
        <div class="actions" style="display:flex;gap:8px;">
            <form action="{{ route('sessions.sync') }}" method="POST" style="display:inline;">
                @csrf
                <button type="submit" class="btn btn-secondary btn-sm" title="Sweep and terminate expired sessions">
                    ↻ Sweep Expired Sessions
                </button>
            </form>
            <button type="button" class="btn btn-secondary btn-sm" onclick="window.location.reload()">
                ↻ Refresh Live
            </button>
        </div>
    </div>

    <!-- Simulated Gateway Banner -->
    @if($gatewayInfo['mock_mode'] ?? true)
        <div class="card" style="background:#f8fafc;padding:12px 18px;margin-bottom:20px;border-left:4px solid var(--primary);font-size:12px;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:10px;">
            <div>
                <strong>⚙ Simulated Hotspot Gateway:</strong> <code>{{ $gatewayInfo['driver'] }}</code> · 
                <span>Status: <span class="badge badge-active">Online</span></span> · 
                <span>Sessions are managed via local simulated gateway leases.</span>
            </div>
            <a href="{{ auth()->user()->getEffectiveUser()->portal_url }}" target="_blank" class="btn btn-secondary btn-sm" style="font-size:11px;">
                Open Captive Portal ↗
            </a>
        </div>
    @endif

    <!-- Active Sessions Count Badge -->
    <div class="grid stats" style="grid-template-columns: repeat(3, minmax(0, 1fr));">
        <article class="card stat">
            <div class="stat-head"><span>Live Online Devices</span><span class="stat-icon" style="background:#e5f8ef;color:#16a673">⌁</span></div>
            <div class="stat-value">{{ $sessions->where('status', 'online')->count() }}</div>
            <div class="stat-foot">Connected to Access Points</div>
        </article>
        <article class="card stat">
            <div class="stat-head"><span>Total Data Download</span><span class="stat-icon">↓</span></div>
            <div class="stat-value">{{ round($sessions->sum('bytes_down') / (1024 * 1024 * 1024), 2) }} GB</div>
            <div class="stat-foot">Cumulative active sessions</div>
        </article>
        <article class="card stat">
            <div class="stat-head"><span>Total Data Upload</span><span class="stat-icon">↑</span></div>
            <div class="stat-value">{{ round($sessions->sum('bytes_up') / (1024 * 1024), 2) }} MB</div>
            <div class="stat-foot">Cumulative active sessions</div>
        </article>
    </div>

    <section class="card table-panel">
        <div class="panel-head">
            <div>
                <h3>Connected Clients & Gateway Leases</h3>
                <p>Live captive portal sessions authenticated through voucher credentials</p>
            </div>
        </div>

        <div class="table-scroll">
            <table class="table">
                <thead>
                    <tr>
                        <th>Voucher Code</th>
                        <th>Package</th>
                        <th>Client IP</th>
                        <th>Client MAC</th>
                        <th>Device Name</th>
                        <th>Device Limit</th>
                        <th>Downloaded</th>
                        <th>Connected At</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($sessions as $session)
                        <tr>
                            <td>
                                <strong style="font-family:monospace;">{{ $session->voucher->code ?? 'N/A' }}</strong>
                            </td>
                            <td>{{ $session->voucher->package->name ?? 'Standard' }}</td>
                            <td><code>{{ $session->ip_address }}</code></td>
                            <td><small class="muted" style="font-family:monospace;">{{ $session->mac_address }}</small></td>
                            <td>{{ $session->device_name }}</td>
                            <td>
                                <span class="badge badge-available">
                                    {{ $session->voucher?->devices?->count() ?? 1 }} / {{ $session->voucher?->device_limit ?? 1 }}
                                </span>
                            </td>
                            <td>
                                <strong>{{ round($session->bytes_down / (1024 * 1024), 1) }} MB</strong>
                                <small class="muted">/ {{ round($session->bytes_up / (1024 * 1024), 1) }} MB</small>
                            </td>
                            <td>{{ $session->connected_at ? $session->connected_at->diffForHumans() : 'Just now' }}</td>
                            <td>
                                <span class="badge {{ $session->status === 'online' ? 'badge-active' : 'badge-expired' }}">
                                    {{ ucfirst($session->status) }}
                                </span>
                            </td>
                            <td>
                                @if($session->status === 'online')
                                    <form action="{{ route('sessions.disconnect', $session->id) }}" method="POST" onsubmit="return confirm('Disconnect this client?')">
                                        @csrf
                                        <button type="submit" class="btn btn-secondary btn-sm" style="color:var(--danger);border-color:#fca5a5;" title="Kick Session">
                                            Disconnect
                                        </button>
                                    </form>
                                @else
                                    <span style="font-size:11px;color:var(--muted);">Terminated</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" style="text-align:center;padding:32px;color:var(--muted);">
                                No devices currently online. Sell a voucher or login from the captive portal to start a session.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</section>
@endsection
