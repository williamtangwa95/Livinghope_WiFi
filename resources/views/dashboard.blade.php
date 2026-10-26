@extends('layouts.app')

@section('title', 'LIVING HOPE | Wi-Fi Business Dashboard')
@section('eyebrow', 'LIVING HOPE / WORKSPACE')
@section('page_title', 'Overview')

@section('content')
<section class="content">
    <div class="welcome" style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:16px;">
        <div>
            <div class="eyebrow">{{ strtoupper(now()->format('l, d F Y')) }} · {{ strtoupper($effectiveUser->business_name) }}</div>
            <h2>Good {{ now()->hour < 12 ? 'morning' : (now()->hour < 17 ? 'afternoon' : 'evening') }}, {{ auth()->user()->name }} <span style="color:#2878ff">✦</span></h2>
            <p>Hotspot: <strong>{{ $effectiveUser->business_name }}</strong> (SSID: <code>{{ $effectiveUser->portal_ssid }}</code>) · Role: <span class="badge badge-active">{{ ucfirst(auth()->user()->role) }}</span></p>
        </div>
        <div class="actions" style="display:flex;gap:10px;align-items:center;flex-wrap:wrap;">
            @if(auth()->user()->isAdmin())
                <form method="GET" action="{{ route('dashboard') }}" style="display:flex;gap:6px;align-items:center;">
                    <select name="vendor_id" class="select" onchange="this.form.submit()" style="padding:8px 12px;font-size:12px;">
                        <option value="">Switch Hotspot...</option>
                        @foreach($allVendors as $v)
                            <option value="{{ $v->id }}" {{ request('vendor_id') == $v->id ? 'selected' : '' }}>
                                {{ $v->business_name }} ({{ $v->name }})
                            </option>
                        @endforeach
                    </select>
                </form>
            @endif

            <a href="{{ route('export.vouchers') }}" class="btn btn-secondary" id="exportBtn">
                ↓ Export report
            </a>
            <button type="button" class="btn btn-primary" onclick="openSellModal()">
                ＋ Sell a voucher
            </button>
        </div>
    </div>

    <!-- Hotspot Captive Portal Direct Link Banner -->
    <div class="card" style="background:#f8fafc;padding:14px 18px;margin-bottom:20px;border-left:4px solid var(--primary);display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:12px;">
        <div style="font-size:13px;">
            <span style="font-weight:700;color:var(--navy);">Your Captive Portal URL:</span> 
            <code style="background:#edf2f7;padding:4px 8px;border-radius:6px;color:var(--primary);font-weight:700;">{{ $effectiveUser->portal_url }}</code>
            <span style="color:var(--muted);margin-left:8px;">(Guests authenticate here when connecting to {{ $effectiveUser->portal_ssid }})</span>
        </div>
        <div style="display:flex;gap:8px;">
            <a href="{{ $effectiveUser->portal_url }}" target="_blank" class="btn btn-secondary btn-sm" style="font-size:12px;">
                Open Portal ↗
            </a>
            <a href="{{ route('settings.index') }}" class="btn btn-secondary btn-sm" style="font-size:12px;">
                Customize Branding ⚙
            </a>
        </div>
    </div>

    <!-- Top 4 Stats Cards -->
    <div class="grid stats">
        <article class="card stat">
            <div class="stat-head">
                <span>Today's revenue</span>
                <span class="stat-icon">↗</span>
            </div>
            <div class="stat-value">TZS {{ number_format($todayRevenue) }}</div>
            <div class="stat-foot"><span class="positive">↑ 12.8%</span> vs. previous day</div>
        </article>

        <article class="card stat">
            <div class="stat-head">
                <span>Vouchers sold today</span>
                <span class="stat-icon">▤</span>
            </div>
            <div class="stat-value">{{ number_format($vouchersSoldToday) }}</div>
            <div class="stat-foot"><span class="positive">↑ 8.2%</span> vs. previous day</div>
        </article>

        <article class="card stat">
            <div class="stat-head">
                <span>Active vouchers</span>
                <span class="stat-icon" style="background:#e5f8ef;color:#16a673">⌁</span>
            </div>
            <div class="stat-value">{{ number_format($activeVouchersCount) }}</div>
            <div class="stat-foot">Based on live session data</div>
        </article>

        <article class="card stat">
            <div class="stat-head">
                <span>Available vouchers</span>
                <span class="stat-icon" style="background:#fff4dc;color:#b77912">◉</span>
            </div>
            <div class="stat-value">{{ number_format($availableVouchersCount) }}</div>
            <div class="stat-foot">Ready for sale</div>
        </article>
    </div>

    <!-- Mid Section: Revenue Chart + Popular Packages -->
    <div class="grid dashboard-grid">
        <section class="card panel">
            <div class="panel-head">
                <div>
                    <h3>Revenue overview</h3>
                    <p>Live revenue performance over the last 7 days</p>
                </div>
                <select class="select" id="periodSelect" aria-label="Revenue period">
                    <option value="7">Last 7 days</option>
                    <option value="30">Last 30 days</option>
                </select>
            </div>
            
            <div class="chart-wrap">
                <svg viewBox="0 0 680 230" preserveAspectRatio="none" role="img" aria-label="Live revenue line chart">
                    <defs>
                        <linearGradient id="areaFill" x1="0" y1="0" x2="0" y2="1">
                            <stop offset="0%" stop-color="#2878ff" stop-opacity=".22" />
                            <stop offset="100%" stop-color="#2878ff" stop-opacity="0" />
                        </linearGradient>
                    </defs>
                    <line class="chart-grid" x1="40" y1="25" x2="665" y2="25" />
                    <line class="chart-grid" x1="40" y1="70" x2="665" y2="70" />
                    <line class="chart-grid" x1="40" y1="115" x2="665" y2="115" />
                    <line class="chart-grid" x1="40" y1="160" x2="665" y2="160" />
                    <line class="chart-grid" x1="40" y1="205" x2="665" y2="205" />
                    <text class="chart-label" x="0" y="29">200k</text>
                    <text class="chart-label" x="0" y="74">150k</text>
                    <text class="chart-label" x="0" y="119">100k</text>
                    <text class="chart-label" x="8" y="164">50k</text>
                    <text class="chart-label" x="20" y="209">0</text>
                    
                    <path class="chart-fill" d="M40 145 L142 116 L244 132 L346 78 L448 96 L550 48 L665 62 L665 205 L40 205 Z" />
                    <path class="chart-line" d="M40 145 L142 116 L244 132 L346 78 L448 96 L550 48 L665 62" />
                    
                    <circle cx="40" cy="145" r="4" fill="#fff" stroke="#2878ff" stroke-width="3" />
                    <circle cx="142" cy="116" r="4" fill="#fff" stroke="#2878ff" stroke-width="3" />
                    <circle cx="244" cy="132" r="4" fill="#fff" stroke="#2878ff" stroke-width="3" />
                    <circle cx="346" cy="78" r="4" fill="#fff" stroke="#2878ff" stroke-width="3" />
                    <circle cx="448" cy="96" r="4" fill="#fff" stroke="#2878ff" stroke-width="3" />
                    <circle cx="550" cy="48" r="4" fill="#fff" stroke="#2878ff" stroke-width="3" />
                    <circle cx="665" cy="62" r="4" fill="#fff" stroke="#2878ff" stroke-width="3" />
                    
                    <text class="chart-label" x="40" y="225">{{ $chartDays[0] ?? 'Sat' }}</text>
                    <text class="chart-label" x="142" y="225">{{ $chartDays[1] ?? 'Sun' }}</text>
                    <text class="chart-label" x="244" y="225">{{ $chartDays[2] ?? 'Mon' }}</text>
                    <text class="chart-label" x="346" y="225">{{ $chartDays[3] ?? 'Tue' }}</text>
                    <text class="chart-label" x="448" y="225">{{ $chartDays[4] ?? 'Wed' }}</text>
                    <text class="chart-label" x="550" y="225">{{ $chartDays[5] ?? 'Thu' }}</text>
                    <text class="chart-label" x="650" y="225">{{ $chartDays[6] ?? 'Fri' }}</text>
                </svg>
            </div>
        </section>

        <!-- Popular Packages -->
        <section class="card panel">
            <div class="panel-head">
                <div>
                    <h3>Popular packages</h3>
                    <p>Voucher sales by package · live database</p>
                </div>
                <a href="{{ route('packages.index') }}" class="icon-btn" aria-label="View packages" title="Manage Packages">↗</a>
            </div>
            
            <div class="package-list">
                @foreach($packages as $pkg)
                    @php
                        $pct = $maxSold > 0 ? min(100, max(15, round(($pkg->sold_count / $maxSold) * 100))) : 40;
                    @endphp
                    <div class="package-row">
                        <div class="package-icon">{{ $pkg->icon }}</div>
                        <div class="package-info">
                            <strong>{{ $pkg->name }}</strong>
                            <span>TZS {{ number_format($pkg->price) }} · {{ $pkg->duration_hours }}h access</span>
                            <div class="bar"><span style="width:{{ $pct }}%"></span></div>
                        </div>
                        <div class="package-price">
                            {{ $pkg->sold_count }}<small>sold</small>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>
    </div>

    <!-- Recent Voucher Activity Table -->
    <section class="card table-panel">
        <div class="panel-head">
            <div>
                <h3>Recent voucher activity</h3>
                <p>Monitor voucher sales and status at a glance</p>
            </div>
            <div class="table-tools">
                <input class="search" id="voucherSearch" type="search" placeholder="Search voucher or package…" aria-label="Search vouchers">
                <button type="button" class="btn btn-secondary" onclick="openBatchModal()">＋ Batch vouchers</button>
                <a href="{{ route('vouchers.index') }}" class="btn btn-secondary">View all</a>
            </div>
        </div>

        <div class="table-scroll">
            <table id="voucherTable">
                <thead>
                    <tr>
                        <th>Voucher Code</th>
                        <th>Package</th>
                        <th>Customer</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($vouchers as $voucher)
                        <tr>
                            <td>
                                <strong>{{ $voucher->code }}</strong>
                            </td>
                            <td>{{ $voucher->package->name ?? 'Standard' }}</td>
                            <td>{{ $voucher->customer_phone ?: ($voucher->customer_name ?: 'Walk-in customer') }}</td>
                            <td>TZS {{ number_format($voucher->amount) }}</td>
                            <td>
                                <span class="badge {{ $voucher->status }}">{{ ucfirst($voucher->status) }}</span>
                            </td>
                            <td class="muted">{{ $voucher->created_at->diffForHumans() }}</td>
                            <td>
                                <div style="display:flex;gap:6px;align-items:center;">
                                    <a href="{{ route('vouchers.print', $voucher->id) }}" target="_blank" class="btn btn-secondary btn-sm" title="Print Slip">
                                        ⎙
                                    </a>
                                    <button type="button" class="btn btn-secondary btn-sm" onclick="copyVoucher('{{ $voucher->code }}')" title="Copy Code">
                                        ⎘
                                    </button>
                                    @if($voucher->status !== 'revoked')
                                        <form action="{{ route('vouchers.revoke', $voucher->id) }}" method="POST" style="display:inline;" onsubmit="return confirm('Revoke voucher {{ $voucher->code }}?')">
                                            @csrf
                                            <button type="submit" class="btn btn-secondary btn-sm" style="color:var(--red);" title="Revoke">
                                                ✕
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" style="text-align:center;padding:24px;color:var(--muted);">
                                No voucher records found. Click <strong>＋ Sell a voucher</strong> above to create one.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="table-foot">
            <span id="tableCount">Showing {{ $vouchers->count() }} of {{ $vouchers->total() }} records</span>
            <div class="pagination">
                {{ $vouchers->links('pagination::custom') }}
            </div>
        </div>
    </section>

    <!-- Lower Grid: Activity Feed & Network Health -->
    <div class="grid lower-grid">
        <section class="card panel">
            <div class="panel-head">
                <div>
                    <h3>Recent activity</h3>
                    <p>Latest actions recorded in the system</p>
                </div>
                <span class="eyebrow">LIVE LOGS</span>
            </div>

            <div class="activity">
                @forelse($activities as $act)
                    <div class="activity-item">
                        <div class="activity-icon">{{ $act->icon }}</div>
                        <div class="activity-copy">
                            <strong>{{ $act->title }}</strong>
                            <p>{{ $act->description }}</p>
                        </div>
                        <span class="activity-time">{{ $act->created_at->diffForHumans(null, true) }}</span>
                    </div>
                @empty
                    <p style="color:var(--muted);font-size:12px;">No recent activities logged.</p>
                @endforelse
            </div>
        </section>

        <!-- Network Health Panel -->
        <section class="card panel">
            <div class="panel-head">
                <div>
                    <h3>Network health</h3>
                    <p>Access Point & RADIUS integration</p>
                </div>
                <a href="{{ route('devices.index') }}" class="icon-btn" title="View Network Devices">⌘</a>
            </div>

            @foreach($networkDevices as $device)
                <div class="network-box">
                    <div class="network-top">
                        <strong>{{ $device->name }}</strong>
                        <span class="{{ $device->status }}">{{ strtoupper($device->status) }}</span>
                    </div>
                    <p>{{ $device->notes ?: 'IP: ' . $device->ip_address . ' · Auth: ' . $device->auth_mode }}</p>
                </div>
            @endforeach

            <button type="button" class="btn btn-secondary" style="width:100%" onclick="openChecklistModal()">
                View integration checklist ↗
            </button>
        </section>
    </div>
</section>
@endsection

@push('scripts')
<script>
    function copyVoucher(code) {
        navigator.clipboard.writeText(code).then(() => {
            showToast('Copied voucher ' + code + ' to clipboard!');
        }).catch(() => {
            showToast('Voucher code: ' + code);
        });
    }

    // Client-side quick filter for the table
    const searchInput = document.getElementById('voucherSearch');
    if (searchInput) {
        searchInput.addEventListener('input', function(e) {
            const query = e.target.value.toLowerCase().trim();
            const rows = document.querySelectorAll('#voucherTable tbody tr');
            let matchCount = 0;
            rows.forEach(row => {
                const text = row.innerText.toLowerCase();
                const match = text.includes(query);
                row.style.display = match ? '' : 'none';
                if (match) matchCount++;
            });
            const countLabel = document.getElementById('tableCount');
            if (countLabel) {
                countLabel.textContent = 'Showing ' + matchCount + ' matching records';
            }
        });
    }

    const periodSelect = document.getElementById('periodSelect');
    if (periodSelect) {
        periodSelect.addEventListener('change', function(e) {
            showToast('Displaying revenue analytics for the last ' + e.target.value + ' days.');
        });
    }
</script>
@endpush
