@extends('layouts.app')

@section('title', 'Wi-Fi Packages | LIVING HOPE Wi-Fi')
@section('eyebrow', 'LIVING HOPE / PACKAGES')
@section('page_title', 'Wi-Fi Packages')

@section('content')
<section class="content">
    <div class="welcome">
        <div>
            <div class="eyebrow">TARIFFS & PROFILES</div>
            <h2>Wi-Fi Packages</h2>
            <p>Configure durations, speeds, device limits and pricing for customer vouchers.</p>
        </div>
        <div class="actions">
            <button type="button" class="btn btn-primary" onclick="openPackageModal()">
                ＋ Create New Package
            </button>
        </div>
    </div>

    <!-- Package Cards Grid -->
    <div class="grid" style="grid-template-columns:repeat(auto-fit, minmax(280px, 1fr));gap:20px;">
        @foreach($packages as $pkg)
            <div class="card panel" style="display:flex;flex-direction:column;justify-content:space-between;border-top:4px solid var(--blue);">
                <div>
                    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;">
                        <span class="badge {{ $pkg->is_active ? 'active' : 'expired' }}">{{ $pkg->badge_label }}</span>
                        <div class="stat-icon" style="margin:0;">{{ $pkg->icon }}</div>
                    </div>

                    <h3 style="font-size:20px;margin:0 0 6px;">{{ $pkg->name }}</h3>
                    <div style="font-size:26px;font-weight:900;letter-spacing:-.04em;color:var(--ink);margin-bottom:14px;">
                        TZS {{ number_format($pkg->price) }}
                        <small style="font-size:12px;font-weight:600;color:var(--muted);">/ {{ $pkg->duration_hours }}h</small>
                    </div>

                    <div style="background:#f8fafc;padding:12px;border-radius:12px;font-size:12px;line-height:1.7;color:#475569;margin-bottom:16px;">
                        <div><strong>Duration:</strong> {{ $pkg->duration_hours }} hours</div>
                        <div><strong>Speed Profile:</strong> {{ $pkg->speed_limit }}</div>
                        <div><strong>Simultaneous Devices:</strong> {{ $pkg->device_limit }} device(s)</div>
                        <div><strong>Total Sold:</strong> {{ $pkg->vouchers_count }} vouchers</div>
                    </div>

                    <p style="font-size:12px;color:var(--muted);line-height:1.6;margin:0 0 16px;">
                        {{ $pkg->description }}
                    </p>
                </div>

                <div style="display:flex;gap:8px;">
                    <button type="button" class="btn btn-secondary" style="flex:1;" onclick="editPackage({{ json_encode($pkg) }})">
                        ✎ Edit Tariffs
                    </button>
                    <button type="button" class="btn btn-primary" onclick="quickSell({{ $pkg->id }})">
                        ＋ Sell
                    </button>
                </div>
            </div>
        @endforeach
    </div>
</section>

<!-- Create / Edit Package Modal -->
<div class="modal-backdrop" id="packageModal">
    <div class="modal">
        <div class="modal-head">
            <h3 id="packageModalTitle">Create Wi-Fi Package</h3>
            <button type="button" class="modal-close" onclick="closePackageModal()">✕</button>
        </div>
        <form id="packageForm" action="{{ route('packages.store') }}" method="POST">
            @csrf
            <div class="form-group">
                <label>Package Name *</label>
                <input type="text" class="field" name="name" id="pkgName" placeholder="e.g. 12 Hours or Weekend Pass" required>
            </div>

            <div class="form-group" style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                <div>
                    <label>Duration (Hours) *</label>
                    <input type="number" class="field" name="duration_hours" id="pkgDuration" min="1" placeholder="e.g. 12" required>
                </div>
                <div>
                    <label>Price (TZS) *</label>
                    <input type="number" class="field" name="price" id="pkgPrice" min="100" step="100" placeholder="e.g. 1500" required>
                </div>
            </div>

            <div class="form-group" style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                <div>
                    <label>Speed Limit</label>
                    <input type="text" class="field" name="speed_limit" id="pkgSpeed" value="5 Mbps / 2 Mbps">
                </div>
                <div>
                    <label>Badge Label</label>
                    <input type="text" class="field" name="badge_label" id="pkgBadge" placeholder="e.g. PROMO">
                </div>
            </div>

            <div class="form-group">
                <label>Description</label>
                <textarea class="field" name="description" id="pkgDesc" rows="3" placeholder="Access terms and details..."></textarea>
            </div>

            <div style="display:flex;gap:10px;justify-content:flex-end;margin-top:20px;">
                <button type="button" class="btn btn-secondary" onclick="closePackageModal()">Cancel</button>
                <button type="submit" class="btn btn-primary" id="pkgSubmitBtn">Save Package</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function openPackageModal() {
        document.getElementById('packageModalTitle').textContent = 'Create Wi-Fi Package';
        document.getElementById('packageForm').action = "{{ route('packages.store') }}";
        document.getElementById('pkgName').value = '';
        document.getElementById('pkgDuration').value = '';
        document.getElementById('pkgPrice').value = '';
        document.getElementById('pkgSpeed').value = '5 Mbps / 2 Mbps';
        document.getElementById('pkgBadge').value = 'PASS';
        document.getElementById('pkgDesc').value = '';
        document.getElementById('packageModal').classList.add('open');
    }

    function editPackage(pkg) {
        document.getElementById('packageModalTitle').textContent = 'Edit Package: ' + pkg.name;
        document.getElementById('packageForm').action = "/packages/" + pkg.id;
        document.getElementById('pkgName').value = pkg.name;
        document.getElementById('pkgDuration').value = pkg.duration_hours;
        document.getElementById('pkgPrice').value = pkg.price;
        document.getElementById('pkgSpeed').value = pkg.speed_limit || '';
        document.getElementById('pkgBadge').value = pkg.badge_label || '';
        document.getElementById('pkgDesc').value = pkg.description || '';
        document.getElementById('packageModal').classList.add('open');
    }

    function closePackageModal() {
        document.getElementById('packageModal').classList.remove('open');
    }

    function quickSell(pkgId) {
        const select = document.getElementById('sellPackageSelect');
        if (select) select.value = pkgId;
        openSellModal();
    }
</script>
@endpush
