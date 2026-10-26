@extends('layouts.app')

@section('title', 'Reports & Analytics | LIVING HOPE Wi-Fi')
@section('eyebrow', 'FINANCE & PERFORMANCE')
@section('page_title', 'Reports & Analytics')

@section('content')
<div class="content">
    <!-- Header with Filters & Export -->
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;flex-wrap:wrap;gap:12px;">
        <div>
            <h2 style="margin:0 0 4px 0;font-size:20px;font-weight:700;color:var(--navy);">Sales & Network Insights</h2>
            <p style="margin:0;font-size:13px;color:var(--muted);">Track voucher sales, popular packages, revenue growth, and live bandwidth usage.</p>
        </div>
        
        <div style="display:flex;gap:10px;align-items:center;flex-wrap:wrap;">
            <div class="filter-tabs" style="display:flex;gap:4px;background:#e2e8f0;padding:4px;border-radius:12px;">
                <a href="{{ route('reports.index', ['range' => 'today']) }}" class="btn btn-sm {{ $range === 'today' ? 'btn-primary' : 'btn-secondary' }}" style="padding:6px 12px;font-size:12px;">Today</a>
                <a href="{{ route('reports.index', ['range' => '7days']) }}" class="btn btn-sm {{ $range === '7days' ? 'btn-primary' : 'btn-secondary' }}" style="padding:6px 12px;font-size:12px;">Last 7 Days</a>
                <a href="{{ route('reports.index', ['range' => '30days']) }}" class="btn btn-sm {{ $range === '30days' ? 'btn-primary' : 'btn-secondary' }}" style="padding:6px 12px;font-size:12px;">Last 30 Days</a>
                <a href="{{ route('reports.index', ['range' => 'thismonth']) }}" class="btn btn-sm {{ $range === 'thismonth' ? 'btn-primary' : 'btn-secondary' }}" style="padding:6px 12px;font-size:12px;">This Month</a>
            </div>

            <a href="{{ route('export.vouchers') }}" class="btn btn-secondary btn-sm" style="display:inline-flex;align-items:center;gap:6px;">
                <span>↗</span> Download CSV
            </a>
        </div>
    </div>

    <!-- KPI Summary Cards -->
    <div class="grid grid-4" style="margin-bottom:24px;">
        <div class="card stat">
            <span class="stat-label">TOTAL REVENUE ({{ strtoupper($range) }})</span>
            <div class="stat-value">TZS {{ number_format($totalRevenue) }}</div>
            <div class="stat-meta">
                <span class="stat-trend trend-up">↑ Live</span>
                <span>From {{ $totalSold }} vouchers</span>
            </div>
        </div>

        <div class="card stat">
            <span class="stat-label">ACTIVE CLIENTS NOW</span>
            <div class="stat-value">{{ $totalOnlineClients }}</div>
            <div class="stat-meta">
                <span class="stat-trend trend-up">● Connected</span>
                <span>{{ $activeVouchersCount }} active vouchers</span>
            </div>
        </div>

        <div class="card stat">
            <span class="stat-label">TOTAL DOWNLOADED</span>
            <div class="stat-value">{{ number_format($totalBytesDown / (1024 * 1024 * 1024), 2) }} <small style="font-size:14px;">GB</small></div>
            <div class="stat-meta">
                <span>Upload: {{ number_format($totalBytesUp / (1024 * 1024), 1) }} MB</span>
            </div>
        </div>

        <div class="card stat">
            <span class="stat-label">AVAILABLE STOCK</span>
            <div class="stat-value">{{ $availableCount }}</div>
            <div class="stat-meta">
                <span>{{ $expiredCount }} expired · {{ $revokedCount }} revoked</span>
            </div>
        </div>
    </div>

    <div class="grid grid-2" style="margin-bottom:24px;">
        <!-- Daily Trend Table -->
        <div class="card">
            <div style="padding:16px 20px;border-bottom:1px solid var(--line);display:flex;justify-content:space-between;align-items:center;">
                <h3 style="margin:0;font-size:16px;font-weight:700;">Revenue Timeline</h3>
                <span class="eyebrow">DAILY BREAKDOWN</span>
            </div>
            <div class="table-wrap">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Vouchers Sold</th>
                            <th>Revenue Generated</th>
                            <th>Performance</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $maxDaily = collect($dailyData)->max('revenue') ?: 1;
                        @endphp
                        @foreach($dailyData as $day)
                            <tr>
                                <td><strong>{{ $day['date'] }}</strong></td>
                                <td>{{ $day['count'] }} sold</td>
                                <td><strong>TZS {{ number_format($day['revenue']) }}</strong></td>
                                <td style="width:140px;">
                                    <div style="background:#e2e8f0;border-radius:6px;height:8px;overflow:hidden;">
                                        <div style="background:var(--primary);height:100%;width:{{ min(100, round(($day['revenue'] / $maxDaily) * 100)) }}%;"></div>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Package Performance Breakdown -->
        <div class="card">
            <div style="padding:16px 20px;border-bottom:1px solid var(--line);display:flex;justify-content:space-between;align-items:center;">
                <h3 style="margin:0;font-size:16px;font-weight:700;">Package Performance</h3>
                <span class="eyebrow">BY DURATION</span>
            </div>
            <div style="padding:20px;">
                @php
                    $maxPkgSold = $packages->max('sold_count') ?: 1;
                @endphp
                @forelse($packages as $pkg)
                    <div style="margin-bottom:18px;">
                        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:6px;font-size:13px;">
                            <div>
                                <strong>{{ $pkg->name }}</strong>
                                <span style="color:var(--muted);margin-left:6px;">({{ $pkg->formatted_price }})</span>
                            </div>
                            <span class="badge badge-available">{{ $pkg->sold_count }} sold</span>
                        </div>
                        <div style="background:#e2e8f0;border-radius:8px;height:10px;overflow:hidden;">
                            <div style="background:linear-gradient(90deg, var(--primary), #38bdf8);height:100%;width:{{ min(100, round(($pkg->sold_count / $maxPkgSold) * 100)) }}%;"></div>
                        </div>
                        <div style="display:flex;justify-content:space-between;font-size:11px;color:var(--muted);margin-top:4px;">
                            <span>Duration: {{ $pkg->duration_hours }}h</span>
                            <span>Speed: {{ $pkg->speed_limit }}</span>
                        </div>
                    </div>
                @empty
                    <p style="text-align:center;color:var(--muted);padding:24px 0;">No packages created yet.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
