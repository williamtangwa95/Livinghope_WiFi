<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#0b1220">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'LIVING HOPE | Wi-Fi Management System')</title>
    <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
    @stack('styles')
</head>
<body>
    @php
        $currentUser = auth()->user();
        $effectiveUser = $currentUser ? $currentUser->getEffectiveUser() : null;
        $businessName = $effectiveUser ? ($effectiveUser->business_name ?: 'LIVING HOPE') : 'LIVING HOPE';
        $brandMark = $effectiveUser ? ($effectiveUser->portal_logo_text ?: 'LH') : 'LH';
        $brandTheme = $effectiveUser ? ($effectiveUser->portal_theme_color ?: '#2563eb') : '#2563eb';
        $portalUrl = $effectiveUser ? $effectiveUser->portal_url : url('/portal');
        $packagesList = \App\Models\Package::where('is_active', true);
        if ($currentUser && !$currentUser->isAdmin()) {
            $packagesList->where('user_id', $currentUser->getEffectiveUserId());
        }
        $packagesList = $packagesList->get();
    @endphp

    <div class="app">
        <aside class="sidebar" id="sidebar">
            <div class="brand">
                <div class="brand-mark" style="background:{{ $brandTheme }};">{{ $brandMark }}</div>
                <div>
                    <div class="brand-name">{{ $businessName }}</div>
                    <div class="brand-sub">{{ $effectiveUser->portal_ssid ?? 'Wi-Fi Cloud' }}</div>
                </div>
            </div>
            
            <div class="nav-label">WORKSPACE</div>
            
            <nav class="nav" aria-label="Main navigation">
                <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">
                    <span class="ico">◫</span> Overview
                </a>
                
                <button type="button" onclick="openSellModal()">
                    <span class="ico">＋</span> Sell Wi-Fi
                </button>
                
                <a href="{{ route('vouchers.index') }}" class="{{ request()->routeIs('vouchers.*') ? 'active' : '' }}">
                    <span class="ico">▤</span> Voucher Management
                </a>
                
                <a href="{{ route('packages.index') }}" class="{{ request()->routeIs('packages.*') ? 'active' : '' }}">
                    <span class="ico">◈</span> Wi-Fi Packages
                </a>
                
                <a href="{{ route('vouchers.index', ['status' => 'active']) }}">
                    <span class="ico">♙</span> Customers
                </a>
                
                <a href="{{ route('sessions.index') }}" class="{{ request()->routeIs('sessions.*') ? 'active' : '' }}">
                    <span class="ico">⌁</span> Active Sessions
                </a>
                
                <a href="{{ route('devices.index') }}" class="{{ request()->routeIs('devices.*') ? 'active' : '' }}">
                    <span class="ico">⌘</span> Network Devices
                </a>

                <a href="{{ route('reports.index') }}" class="{{ request()->routeIs('reports.*') ? 'active' : '' }}">
                    <span class="ico">▥</span> Reports & Analytics
                </a>

                <a href="{{ route('staff.index') }}" class="{{ request()->routeIs('staff.*') ? 'active' : '' }}">
                    <span class="ico">♧</span> Staff & Permissions
                </a>

                <a href="{{ route('settings.index') }}" class="{{ request()->routeIs('settings.*') ? 'active' : '' }}">
                    <span class="ico">⚙</span> Settings & Portal
                </a>
                
                <a href="{{ route('export.vouchers') }}">
                    <span class="ico">↗</span> Transactions / Export
                </a>
                
                <a href="{{ $portalUrl }}" target="_blank" style="margin-top:12px;background:rgba(56, 189, 248, 0.1);border-color:rgba(56, 189, 248, 0.3);color:#38bdf8;">
                    <span class="ico">↗</span> My Captive Portal
                </a>
            </nav>
            
            <div class="side-bottom">
                <strong><span class="status-dot"></span>Hotspot Engine Live</strong>
                <p>{{ $businessName }} · SSID: {{ $effectiveUser->portal_ssid ?? 'WIFI' }}</p>
                <small>{{ $currentUser->name ?? 'User' }} ({{ ucfirst($currentUser->role ?? 'User') }})</small>
            </div>
        </aside>

        <div class="overlay" id="overlay"></div>

        <main class="main">
            <header class="topbar">
                <div class="top-left">
                    <button class="mobile-menu" id="mobileMenu" aria-label="Open navigation">☰</button>
                    <div>
                        <div class="eyebrow">@yield('eyebrow', strtoupper($businessName) . ' / WORKSPACE')</div>
                        <h1 id="pageTitle">@yield('page_title', 'Overview')</h1>
                    </div>
                </div>

                <div class="top-actions">
                    <a href="{{ $portalUrl }}" target="_blank" class="btn btn-secondary btn-sm" style="display:none;align-items:center;gap:4px;" id="portalQuickBtn">
                        <span>↗</span> Portal
                    </a>

                    <button class="icon-btn" id="helpBtn" aria-label="Help" title="Help" onclick="openHelpModal()">?</button>
                    <button class="icon-btn" id="notifyBtn" aria-label="Notifications" title="Notifications" onclick="showToast('Network hardware is healthy. All services operational.')">♧</button>
                    
                    <div class="avatar" style="background:{{ $brandTheme }};">{{ strtoupper(substr($currentUser->name ?? 'U', 0, 2)) }}</div>
                    <div class="user-info">
                        <strong>{{ $currentUser->name ?? 'Administrator' }}</strong>
                        <span style="font-size:11px;color:var(--muted);">
                            {{ ucfirst($currentUser->role ?? 'owner') }} · {{ $businessName }}
                        </span>
                    </div>

                    <form action="{{ route('logout') }}" method="POST" style="margin-left:8px;">
                        @csrf
                        <button type="submit" class="btn btn-secondary btn-sm" title="Sign Out" style="padding:6px 10px;font-size:12px;color:var(--danger);border-color:#fca5a5;">
                            Sign Out
                        </button>
                    </form>
                </div>
            </header>

            @yield('content')

            <footer class="footer">
                <span>© {{ date('Y') }} {{ $businessName }} · Cloud Wi-Fi Platform</span>
                <span>Role: {{ ucfirst($currentUser->role ?? 'Admin') }} · Portal: <a href="{{ $portalUrl }}" target="_blank" style="color:var(--primary);">{{ $portalUrl }}</a></span>
            </footer>
        </main>
    </div>

    <!-- Sell Voucher Modal -->
    <div class="modal-backdrop" id="sellModal">
        <div class="modal">
            <div class="modal-head">
                <h3>Sell Wi-Fi Voucher</h3>
                <button type="button" class="modal-close" onclick="closeSellModal()">✕</button>
            </div>
            <form action="{{ route('vouchers.sell') }}" method="POST" id="sellVoucherForm">
                @csrf
                <div class="form-group">
                    <label for="sellPackageSelect">Select Wi-Fi Package *</label>
                    <select class="field" name="package_id" id="sellPackageSelect" required>
                        @forelse($packagesList as $pkg)
                            <option value="{{ $pkg->id }}">
                                {{ $pkg->name }} — {{ $pkg->formatted_price }} ({{ $pkg->badge_label }})
                            </option>
                        @empty
                            <option disabled>No active packages. Please create a package first.</option>
                        @endforelse
                    </select>
                </div>

                <div class="form-group">
                    <label for="sellCustomerPhone">Customer Phone Number (Optional)</label>
                    <input type="text" class="field" name="customer_phone" id="sellCustomerPhone" placeholder="+255 7XX XXX XXX">
                </div>

                <div class="form-group">
                    <label for="sellCustomerName">Customer Name (Optional)</label>
                    <input type="text" class="field" name="customer_name" id="sellCustomerName" placeholder="e.g. Walk-in customer or name">
                </div>

                <div class="form-group">
                    <label for="sellCashier">Attendant / Cashier</label>
                    <input type="text" class="field" name="cashier" id="sellCashier" value="{{ $currentUser->name ?? 'Cashier' }}" required>
                </div>

                <div class="form-group" style="display:flex;align-items:center;gap:8px;margin-top:12px;">
                    <input type="checkbox" name="auto_activate" id="autoActivate" value="1">
                    <label for="autoActivate" style="margin:0;cursor:pointer;font-size:12px;text-transform:none;">Activate session immediately upon sale</label>
                </div>

                <div style="display:flex;gap:10px;justify-content:flex-end;margin-top:24px;">
                    <button type="button" class="btn btn-secondary" onclick="closeSellModal()">Cancel</button>
                    <button type="submit" class="btn btn-primary">Generate & Print Slip</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Batch Voucher Generator Modal -->
    <div class="modal-backdrop" id="batchModal">
        <div class="modal">
            <div class="modal-head">
                <h3>Batch Generate Vouchers</h3>
                <button type="button" class="modal-close" onclick="closeBatchModal()">✕</button>
            </div>
            <form action="{{ route('vouchers.batch') }}" method="POST">
                @csrf
                <div class="form-group">
                    <label>Wi-Fi Package *</label>
                    <select class="field" name="package_id" required>
                        @foreach($packagesList as $pkg)
                            <option value="{{ $pkg->id }}">
                                {{ $pkg->name }} — {{ $pkg->formatted_price }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label>Quantity to Generate *</label>
                    <select class="field" name="quantity" required>
                        <option value="10">10 Vouchers</option>
                        <option value="25">25 Vouchers</option>
                        <option value="50" selected>50 Vouchers</option>
                        <option value="100">100 Vouchers</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Batch Tag / Reference (Optional)</label>
                    <input type="text" class="field" name="batch_tag" placeholder="e.g. COUNTER-WEEKEND">
                </div>

                <div style="display:flex;gap:10px;justify-content:flex-end;margin-top:24px;">
                    <button type="button" class="btn btn-secondary" onclick="closeBatchModal()">Cancel</button>
                    <button type="submit" class="btn btn-primary">Generate Batch</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Help Modal -->
    <div class="modal-backdrop" id="helpModal">
        <div class="modal">
            <div class="modal-head">
                <h3>Wi-Fi Platform Help & Documentation</h3>
                <button type="button" class="modal-close" onclick="closeHelpModal()">✕</button>
            </div>
            <div style="font-size:12px;line-height:1.7;color:#334155;">
                <p>Welcome to <strong>{{ $businessName }}</strong> Wi-Fi Management System.</p>
                <p>Each registered hotspot owner controls their own vouchers, pricing packages, cashier staff, and dedicated captive portal.</p>
                <div style="background:#f8fafc;padding:12px;border-radius:10px;border:1px solid #e2e8f0;margin-top:10px;">
                    <strong>Your Captive Portal URL:</strong>
                    <br>
                    <a href="{{ $portalUrl }}" target="_blank" style="color:var(--primary);word-break:break-all;">{{ $portalUrl }}</a>
                </div>
            </div>
            <div style="margin-top:20px;text-align:right;">
                <button type="button" class="btn btn-primary" onclick="closeHelpModal()">Close</button>
            </div>
        </div>
    </div>

    <!-- Toast Notification Element -->
    <div class="toast" id="toast" role="status" aria-live="polite"></div>

    <script>
        const toast = document.getElementById('toast');
        let toastTimer;

        function showToast(message) {
            if (!toast) return;
            toast.textContent = message;
            toast.classList.add('show');
            clearTimeout(toastTimer);
            toastTimer = setTimeout(() => toast.classList.remove('show'), 3500);
        }

        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('overlay');
        const mobileMenu = document.getElementById('mobileMenu');

        if (mobileMenu) {
            mobileMenu.addEventListener('click', () => {
                sidebar.classList.add('open');
                overlay.classList.add('show');
            });
        }

        function closeSidebar() {
            if (sidebar) sidebar.classList.remove('open');
            if (overlay) overlay.classList.remove('show');
        }
        if (overlay) overlay.addEventListener('click', closeSidebar);

        function openSellModal() {
            document.getElementById('sellModal').classList.add('open');
        }
        function closeSellModal() {
            document.getElementById('sellModal').classList.remove('open');
        }

        function openBatchModal() {
            document.getElementById('batchModal').classList.add('open');
        }
        function closeBatchModal() {
            document.getElementById('batchModal').classList.remove('open');
        }

        function openHelpModal() {
            document.getElementById('helpModal').classList.add('open');
        }
        function closeHelpModal() {
            document.getElementById('helpModal').classList.remove('open');
        }

        // Display session flash messages
        @if(session('success'))
            showToast("{{ session('success') }}");
        @endif
        @if(session('error'))
            showToast("{{ session('error') }}");
        @endif
        @if(session('warning'))
            showToast("{{ session('warning') }}");
        @endif
    </script>
    @stack('scripts')
</body>
</html>
