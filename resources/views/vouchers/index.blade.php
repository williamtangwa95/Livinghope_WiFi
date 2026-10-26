@extends('layouts.app')

@section('title', 'Voucher Management | LIVING HOPE Wi-Fi')
@section('eyebrow', 'LIVING HOPE / VOUCHERS')
@section('page_title', 'Voucher Management')

@section('content')
<section class="content">
    <div class="welcome">
        <div>
            <div class="eyebrow">INVENTORY & ACCESS CODES</div>
            <h2>Voucher Management</h2>
            <p>Generate, monitor, print and revoke Wi-Fi access voucher codes.</p>
        </div>
        <div class="actions">
            <button type="button" class="btn btn-secondary" onclick="openBatchModal()">
                ▤ Batch Generate
            </button>
            <button type="button" class="btn btn-primary" onclick="openSellModal()">
                ＋ Sell a Voucher
            </button>
        </div>
    </div>

    <!-- Quick Filter Pills -->
    <div style="display:flex;gap:8px;margin-bottom:18px;flex-wrap:wrap;align-items:center;">
        <a href="{{ route('vouchers.index') }}" class="btn {{ !request('status') ? 'btn-primary' : 'btn-secondary' }} btn-sm">
            All ({{ $stats['total'] }})
        </a>
        <a href="{{ route('vouchers.index', ['status' => 'available']) }}" class="btn {{ request('status') === 'available' ? 'btn-primary' : 'btn-secondary' }} btn-sm">
            Available ({{ $stats['available'] }})
        </a>
        <a href="{{ route('vouchers.index', ['status' => 'active']) }}" class="btn {{ request('status') === 'active' ? 'btn-primary' : 'btn-secondary' }} btn-sm">
            Active ({{ $stats['active'] }})
        </a>
        <a href="{{ route('vouchers.index', ['status' => 'expired']) }}" class="btn {{ request('status') === 'expired' ? 'btn-primary' : 'btn-secondary' }} btn-sm">
            Expired ({{ $stats['expired'] }})
        </a>
        <a href="{{ route('vouchers.index', ['status' => 'revoked']) }}" class="btn {{ request('status') === 'revoked' ? 'btn-primary' : 'btn-secondary' }} btn-sm">
            Revoked ({{ $stats['revoked'] }})
        </a>
    </div>

    <section class="card table-panel" style="margin-top:0;">
        <div class="panel-head">
            <div>
                <h3>All Vouchers</h3>
                <p>Search by voucher code, customer phone, or batch ID</p>
            </div>
            <form action="{{ route('vouchers.index') }}" method="GET" class="table-tools">
                @if(request('status'))
                    <input type="hidden" name="status" value="{{ request('status') }}">
                @endif
                <input class="search" type="search" name="search" value="{{ request('search') }}" placeholder="Search voucher or customer…" aria-label="Search vouchers">
                <button type="submit" class="btn btn-secondary">Search</button>
                @if(request('search'))
                    <a href="{{ route('vouchers.index', ['status' => request('status')]) }}" class="btn btn-secondary">Clear</a>
                @endif
            </form>
        </div>

        <div class="table-scroll">
            <table>
                <thead>
                    <tr>
                        <th>Voucher Code</th>
                        <th>Package</th>
                        <th>Amount</th>
                        <th>Customer</th>
                        <th>Status</th>
                        <th>Batch / Cashier</th>
                        <th>Activated / Expires</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($vouchers as $voucher)
                        <tr>
                            <td>
                                <strong style="font-family:monospace;letter-spacing:.05em;">{{ $voucher->code }}</strong>
                            </td>
                            <td>{{ $voucher->package->name ?? 'Standard' }}</td>
                            <td>TZS {{ number_format($voucher->amount) }}</td>
                            <td>
                                <div>{{ $voucher->customer_phone ?: ($voucher->customer_name ?: 'Walk-in') }}</div>
                                @if($voucher->customer_name && $voucher->customer_phone)
                                    <small class="muted">{{ $voucher->customer_name }}</small>
                                @endif
                            </td>
                            <td>
                                <span class="badge {{ $voucher->status }}">{{ ucfirst($voucher->status) }}</span>
                            </td>
                            <td>
                                <div>{{ $voucher->cashier }}</div>
                                @if($voucher->batch_id)
                                    <small class="muted">{{ $voucher->batch_id }}</small>
                                @endif
                            </td>
                            <td>
                                @if($voucher->activated_at)
                                    <div>{{ $voucher->activated_at->format('M d, H:i') }}</div>
                                    <small class="muted">Expires: {{ $voucher->expires_at ? $voucher->expires_at->format('M d, H:i') : 'N/A' }}</small>
                                @else
                                    <span class="muted">Not activated</span>
                                @endif
                            </td>
                            <td>
                                <div style="display:flex;gap:6px;align-items:center;">
                                    <a href="{{ route('vouchers.print', $voucher->id) }}" target="_blank" class="btn btn-secondary btn-sm" title="Print Slip">
                                        ⎙ Print
                                    </a>
                                    <button type="button" class="btn btn-secondary btn-sm" onclick="copyVoucher('{{ $voucher->code }}')" title="Copy Code">
                                        ⎘ Copy
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
                            <td colspan="8" style="text-align:center;padding:30px;color:var(--muted);">
                                No vouchers found matching your filter criteria.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="table-foot">
            <span>Showing {{ $vouchers->count() }} of {{ $vouchers->total() }} records</span>
            <div class="pagination">
                {{ $vouchers->links('pagination::custom') }}
            </div>
        </div>
    </section>
</section>
@endsection

@push('scripts')
<script>
    function copyVoucher(code) {
        navigator.clipboard.writeText(code).then(() => {
            showToast('Copied voucher code: ' + code);
        }).catch(() => {
            showToast('Voucher code: ' + code);
        });
    }
</script>
@endpush
