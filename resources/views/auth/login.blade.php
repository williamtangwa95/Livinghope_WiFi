<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#0b1220">
    <title>Sign In | LIVING HOPE Wi-Fi Cloud Platform</title>
    <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
    <style>
        /* Base page styling matching dashboard & Wi-Fi packages */
        body {
            margin: 0;
            background-color: var(--bg);
            background-image: 
                radial-gradient(at 0% 0%, rgba(40, 120, 255, 0.08) 0px, transparent 50%),
                radial-gradient(at 100% 0%, rgba(22, 217, 245, 0.08) 0px, transparent 50%),
                radial-gradient(at 50% 100%, rgba(40, 120, 255, 0.04) 0px, transparent 50%);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            font-family: Inter, ui-sans-serif, system-ui, -apple-system, "Segoe UI", sans-serif;
            color: var(--ink);
        }

        /* Top navigation header */
        .auth-topbar {
            height: 72px;
            padding: 0 32px;
            background: #ffffffed;
            border-bottom: 1px solid var(--line);
            display: flex;
            align-items: center;
            justify-content: space-between;
            backdrop-filter: blur(12px);
            position: sticky;
            top: 0;
            z-index: 20;
        }

        .auth-brand {
            display: flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
        }

        .auth-brand-mark {
            width: 42px;
            height: 42px;
            border-radius: 14px;
            display: grid;
            place-items: center;
            background: linear-gradient(135deg, var(--cyan), var(--blue));
            color: var(--navy);
            font-weight: 900;
            font-size: 16px;
            box-shadow: 0 8px 24px #16d9f540;
        }

        .auth-brand-name {
            font-weight: 850;
            letter-spacing: .08em;
            font-size: 14px;
            color: var(--ink);
        }

        .auth-brand-sub {
            color: var(--muted);
            font-size: 11px;
            margin-top: 2px;
        }

        .auth-topbar-actions {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .auth-status-pill {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: #f8fafc;
            border: 1px solid var(--line);
            padding: 6px 12px;
            border-radius: 99px;
            font-size: 11px;
            font-weight: 600;
            color: #475569;
        }

        /* Main layout wrapper */
        .auth-main {
            flex: 1;
            padding: 32px 32px 48px;
            max-width: 1400px;
            width: 100%;
            margin: 0 auto;
            box-sizing: border-box;
        }

        /* Welcome header block */
        .auth-welcome {
            margin-bottom: 26px;
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            gap: 20px;
            flex-wrap: wrap;
        }

        .auth-welcome .eyebrow {
            color: var(--muted);
            font-size: 11px;
            font-weight: 700;
            letter-spacing: .12em;
            text-transform: uppercase;
        }

        .auth-welcome h1 {
            margin: 6px 0 0;
            font-size: clamp(24px, 3.2vw, 31px);
            letter-spacing: -.05em;
            font-weight: 850;
            color: var(--ink);
        }

        .auth-welcome p {
            margin: 6px 0 0;
            color: var(--muted);
            font-size: 13px;
        }

        /* Two-column responsive layout */
        .auth-grid {
            display: grid;
            grid-template-columns: minmax(360px, 450px) minmax(0, 1fr);
            gap: 24px;
            align-items: start;
        }

        @media (max-width: 980px) {
            .auth-grid {
                grid-template-columns: 1fr;
                gap: 24px;
            }
            .auth-topbar {
                padding: 0 16px;
            }
            .auth-main {
                padding: 20px 16px 36px;
            }
            .auth-topbar-actions .auth-status-pill {
                display: none;
            }
        }

        /* Packages-style card */
        .login-card {
            background: white;
            border: 1px solid #e9eef5;
            border-radius: 18px;
            box-shadow: 0 4px 18px #16284806;
            padding: 26px 28px;
            border-top: 4px solid var(--blue);
            transition: transform .2s ease, box-shadow .2s ease;
        }

        .login-card:hover {
            box-shadow: 0 10px 26px #1628480c;
        }

        .card-head-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 12px;
        }

        .login-card h2 {
            font-size: 20px;
            letter-spacing: -.03em;
            font-weight: 850;
            margin: 0 0 6px;
            color: var(--ink);
        }

        .login-card p.card-sub {
            font-size: 12px;
            color: var(--muted);
            line-height: 1.6;
            margin: 0 0 18px;
        }

        /* Form Controls */
        .form-group {
            margin-bottom: 16px;
        }

        .form-group label {
            display: block;
            font-size: 11px;
            font-weight: 750;
            color: #4b5870;
            margin-bottom: 6px;
            text-transform: uppercase;
            letter-spacing: .05em;
        }

        .input-wrap {
            position: relative;
        }

        .field-custom {
            width: 100%;
            border: 1px solid var(--line);
            border-radius: 12px;
            padding: 12px 14px;
            outline: none;
            font-size: 13px;
            background: white;
            color: var(--ink);
            transition: border-color .15s, box-shadow .15s;
            box-sizing: border-box;
        }

        .field-custom:focus {
            border-color: var(--blue);
            box-shadow: 0 0 0 3px #2878ff18;
        }

        .field-custom.has-icon {
            padding-right: 44px;
        }

        .field-toggle-btn {
            position: absolute;
            right: 8px;
            top: 50%;
            transform: translateY(-50%);
            background: transparent;
            border: none;
            color: var(--muted);
            padding: 6px 10px;
            cursor: pointer;
            border-radius: 8px;
            font-size: 15px;
            transition: color .15s;
        }

        .field-toggle-btn:hover {
            color: var(--ink);
        }

        .btn-submit {
            width: 100%;
            padding: 12px 18px;
            font-size: 13px;
            font-weight: 750;
            border-radius: 12px;
            color: white;
            background: linear-gradient(110deg, var(--blue), #155fe0);
            box-shadow: 0 8px 20px #2878ff2c;
            border: 0;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: transform .15s, box-shadow .15s;
            margin-top: 6px;
        }

        .btn-submit:hover {
            transform: translateY(-1px);
            box-shadow: 0 12px 24px #2878ff3c;
        }

        /* Demo Role Cards Grid matching /packages */
        .demo-roles-panel {
            display: flex;
            flex-direction: column;
            gap: 14px;
        }

        .demo-roles-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2px;
        }

        .demo-roles-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
            gap: 16px;
        }

        .demo-role-card {
            background: white;
            border: 1px solid #e9eef5;
            border-radius: 18px;
            box-shadow: 0 4px 18px #16284806;
            padding: 20px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            transition: transform .15s, box-shadow .15s;
        }

        .demo-role-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 22px #1628480e;
        }

        .demo-role-title {
            font-size: 18px;
            font-weight: 800;
            letter-spacing: -.03em;
            color: var(--ink);
            margin: 0 0 4px;
        }

        .demo-role-subtitle {
            font-size: 13px;
            font-weight: 700;
            letter-spacing: -.02em;
            color: var(--muted);
            margin-bottom: 12px;
        }

        .role-specs-box {
            background: #f8fafc;
            border: 1px solid #edf1f6;
            padding: 11px 13px;
            border-radius: 12px;
            font-size: 11.5px;
            line-height: 1.65;
            color: #475569;
            margin-bottom: 16px;
        }

        .role-specs-box div {
            display: flex;
            justify-content: space-between;
            gap: 8px;
        }

        .role-specs-box div strong {
            color: #1e293b;
        }

        .role-actions {
            display: flex;
            gap: 8px;
        }

        /* Alerts */
        .alert-banner {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px 14px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 550;
            margin-bottom: 18px;
        }

        .alert-banner-error {
            background: #fef2f2;
            border: 1px solid #fecaca;
            color: #991b1b;
        }

        .alert-banner-info {
            background: #eff6ff;
            border: 1px solid #bfdbfe;
            color: #1e40af;
        }

        /* Register & Info Callout */
        .register-box {
            margin-top: 20px;
            padding-top: 18px;
            border-top: 1px solid var(--line);
            text-align: center;
            font-size: 12px;
            color: var(--muted);
        }

        /* Highlight animation when clicking Fill */
        @keyframes fieldHighlight {
            0% { background: #f0f7ff; border-color: var(--blue); }
            100% { background: white; border-color: var(--line); }
        }

        .field-flash {
            animation: fieldHighlight 0.9s ease;
        }

        /* Micro-pill info tags */
        .security-badge {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 10px 14px;
            background: #f8fafc;
            border: 1px solid #edf1f6;
            border-radius: 12px;
            font-size: 11px;
            color: #64748b;
            margin-top: 18px;
            line-height: 1.5;
        }
    </style>
</head>
<body>
    <!-- Top Brand Navbar -->
    <header class="auth-topbar">
        <a href="{{ url('/') }}" class="auth-brand">
            <div class="auth-brand-mark">LH</div>
            <div>
                <div class="auth-brand-name">LIVING HOPE</div>
                <div class="auth-brand-sub">Wi-Fi Cloud Management</div>
            </div>
        </a>

        <div class="auth-topbar-actions">
            <div class="auth-status-pill">
                <span class="status-dot"></span>
                Hotspot Engine Live
            </div>
            <a href="{{ route('portal.index') }}" target="_blank" class="btn btn-secondary btn-sm" title="Preview Customer Captive Portal">
                Preview Captive Portal ↗
            </a>
        </div>
    </header>

    <!-- Main Container -->
    <main class="auth-main">
        <!-- Welcome / Page Header Section (Same as /packages) -->
        <div class="auth-welcome">
            <div>
                <div class="eyebrow">AUTHENTICATION & GATEWAY ACCESS</div>
                <h1>Wi-Fi Dashboard Sign In</h1>
                <p>Manage Wi-Fi hotspots, customer tariffs, vouchers, active sessions and captive portals.</p>
            </div>
            <div class="actions">
                <a href="{{ route('register') }}" class="btn btn-secondary">
                    ＋ Register Hotspot Owner
                </a>
            </div>
        </div>

        <!-- Responsive Grid: Login Card on Left + Package-style Demo Cards on Right -->
        <div class="auth-grid">
            <!-- Left Column: Primary Login Card -->
            <div class="login-card panel" id="mainLoginCard">
                <div class="card-head-row">
                    <span class="badge active">SECURE PORTAL</span>
                    <div class="stat-icon" style="margin:0;">🔐</div>
                </div>

                <h2>Credentials Login</h2>
                <p class="card-sub">Sign in with your registered hotspot administrator or staff account.</p>

                @if(session('error'))
                    <div class="alert-banner alert-banner-error">
                        <span>⚠️</span>
                        <span>{{ session('error') }}</span>
                    </div>
                @endif

                @if(session('info'))
                    <div class="alert-banner alert-banner-info">
                        <span>ℹ️</span>
                        <span>{{ session('info') }}</span>
                    </div>
                @endif

                @if($errors->any())
                    <div class="alert-banner alert-banner-error">
                        <span>⚠️</span>
                        <span>{{ $errors->first() }}</span>
                    </div>
                @endif

                <form action="{{ route('login') }}" method="POST" id="loginForm">
                    @csrf
                    <div class="form-group">
                        <label for="emailInput">Email Address *</label>
                        <input 
                            type="email" 
                            id="emailInput" 
                            name="email" 
                            value="{{ old('email', 'admin@livinghope.co.tz') }}" 
                            class="field-custom" 
                            placeholder="admin@livinghope.co.tz" 
                            required 
                            autofocus
                        >
                    </div>

                    <div class="form-group">
                        <label for="passInput">Password *</label>
                        <div class="input-wrap">
                            <input 
                                type="password" 
                                id="passInput" 
                                name="password" 
                                value="admin123" 
                                class="field-custom has-icon" 
                                placeholder="••••••••" 
                                required
                            >
                            <button type="button" class="field-toggle-btn" id="togglePassBtn" title="Toggle password visibility">
                                👁️
                            </button>
                        </div>
                    </div>

                    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:18px;font-size:12px;color:var(--muted);">
                        <label style="display:flex;align-items:center;gap:7px;cursor:pointer;margin:0;font-size:12px;text-transform:none;color:#475569;font-weight:550;">
                            <input type="checkbox" name="remember" value="1" checked style="accent-color:var(--blue);width:15px;height:15px;margin:0;">
                            <span>Keep me signed in</span>
                        </label>
                    </div>

                    <button type="submit" class="btn-submit" id="submitBtn">
                        Sign In to Workspace ➔
                    </button>
                </form>

                <div class="security-badge">
                    <span style="font-size:16px;">🛡️</span>
                    <div>
                        <strong>Encrypted Access</strong><br>
                        Protected by TLS encryption and tenant role isolation.
                    </div>
                </div>

                <div class="register-box">
                    Don't have a Wi-Fi Hotspot account?
                    <div style="margin-top:10px;">
                        <a href="{{ route('register') }}" class="btn btn-secondary" style="width:100%;box-sizing:border-box;">
                            ＋ Create Hotspot Account
                        </a>
                    </div>
                </div>
            </div>

            <!-- Right Column: Instant Role Profiles (Designed in Wi-Fi Packages Card Style) -->
            <div class="demo-roles-panel">
                <div class="demo-roles-header">
                    <div>
                        <div class="eyebrow">DEMO PROFILES</div>
                        <h3 style="font-size:16px;font-weight:800;letter-spacing:-.03em;margin:3px 0 0;color:var(--ink);">
                            Instant One-Click Login Cards
                        </h3>
                    </div>
                    <span style="font-size:11px;color:var(--muted);">Select a role to test</span>
                </div>

                <div class="demo-roles-grid">
                    <!-- Card 1: Super Admin (Blue Top) -->
                    <div class="demo-role-card panel" style="border-top:4px solid var(--blue);">
                        <div>
                            <div class="card-head-row">
                                <span class="badge active">FULL ACCESS</span>
                                <div class="stat-icon" style="margin:0;">👑</div>
                            </div>

                            <div class="demo-role-title">Super Admin</div>
                            <div class="demo-role-subtitle">
                                HQ Master Controller
                            </div>

                            <div class="role-specs-box">
                                <div><span>User:</span> <strong>Administrator</strong></div>
                                <div><span>Email:</span> <strong>admin@livinghope.co.tz</strong></div>
                                <div><span>Role:</span> <strong>Super Administrator</strong></div>
                                <div><span>Scope:</span> <strong>All Hotspots & Gateways</strong></div>
                            </div>
                        </div>

                        <div class="role-actions">
                            <a href="{{ route('demo.login', 'admin') }}" class="btn btn-primary" style="flex:1;">
                                ⚡ 1-Click Login
                            </a>
                            <button type="button" class="btn btn-secondary btn-sm" onclick="fillCredentials('admin@livinghope.co.tz', 'admin123')">
                                Fill
                            </button>
                        </div>
                    </div>

                    <!-- Card 2: Hotspot Owner (Indigo Top) -->
                    <div class="demo-role-card panel" style="border-top:4px solid #6366f1;">
                        <div>
                            <div class="card-head-row">
                                <span class="badge available">PRO OWNER</span>
                                <div class="stat-icon" style="background:#eef2ff;color:#6366f1;margin:0;">☕</div>
                            </div>

                            <div class="demo-role-title">Sunrise Beach Cafe</div>
                            <div class="demo-role-subtitle">
                                Hotspot Owner (Verified)
                            </div>

                            <div class="role-specs-box">
                                <div><span>User:</span> <strong>David K. Mwalimu</strong></div>
                                <div><span>Email:</span> <strong>vendor@cafe.co.tz</strong></div>
                                <div><span>Role:</span> <strong>Hotspot Business Owner</strong></div>
                                <div><span>Scope:</span> <strong>Vouchers, Portal & Tariffs</strong></div>
                            </div>
                        </div>

                        <div class="role-actions">
                            <a href="{{ route('demo.login', 'vendor') }}" class="btn btn-primary" style="flex:1;background:linear-gradient(110deg,#6366f1,#4f46e5);box-shadow:0 8px 20px #6366f12c;">
                                ⚡ 1-Click Login
                            </a>
                            <button type="button" class="btn btn-secondary btn-sm" onclick="fillCredentials('vendor@cafe.co.tz', 'password123')">
                                Fill
                            </button>
                        </div>
                    </div>

                    <!-- Card 3: Staff Cashier (Cyan/Sky Top) -->
                    <div class="demo-role-card panel" style="border-top:4px solid #0ea5e9;">
                        <div>
                            <div class="card-head-row">
                                <span class="badge" style="background:#e0f2fe;color:#0284c7;">STAFF DESK</span>
                                <div class="stat-icon" style="background:#e0f2fe;color:#0284c7;margin:0;">💼</div>
                            </div>

                            <div class="demo-role-title">Staff Cashier</div>
                            <div class="demo-role-subtitle">
                                Sales & Voucher Desk
                            </div>

                            <div class="role-specs-box">
                                <div><span>User:</span> <strong>Asha M.</strong></div>
                                <div><span>Email:</span> <strong>cashier@livinghope.co.tz</strong></div>
                                <div><span>Role:</span> <strong>Sales Cashier</strong></div>
                                <div><span>Scope:</span> <strong>Issue Vouchers & Print Slips</strong></div>
                            </div>
                        </div>

                        <div class="role-actions">
                            <a href="{{ route('demo.login', 'cashier') }}" class="btn btn-primary" style="flex:1;background:linear-gradient(110deg,#0ea5e9,#0284c7);box-shadow:0 8px 20px #0ea5e92c;">
                                ⚡ 1-Click Login
                            </a>
                            <button type="button" class="btn btn-secondary btn-sm" onclick="fillCredentials('cashier@livinghope.co.tz', 'password123')">
                                Fill
                            </button>
                        </div>
                    </div>

                    <!-- Card 4: Unverified User (Amber Top) -->
                    <div class="demo-role-card panel" style="border-top:4px solid var(--amber);">
                        <div>
                            <div class="card-head-row">
                                <span class="badge expired">TEST ONBOARDING</span>
                                <div class="stat-icon" style="background:#fffbeb;color:var(--amber);margin:0;">⏳</div>
                            </div>

                            <div class="demo-role-title">Ocean View Lounge</div>
                            <div class="demo-role-subtitle">
                                Pending Email Verification
                            </div>

                            <div class="role-specs-box">
                                <div><span>User:</span> <strong>Juma Rashid</strong></div>
                                <div><span>Email:</span> <strong>newowner@example.com</strong></div>
                                <div><span>Role:</span> <strong>Unverified Hotspot</strong></div>
                                <div><span>Scope:</span> <strong>Tests Email Verification OTP</strong></div>
                            </div>
                        </div>

                        <div class="role-actions">
                            <a href="{{ route('demo.login', 'unverified') }}" class="btn btn-primary" style="flex:1;background:linear-gradient(110deg,var(--amber),#d97706);box-shadow:0 8px 20px #eaa32b2c;">
                                ⚡ Test Flow
                            </a>
                            <button type="button" class="btn btn-secondary btn-sm" onclick="fillCredentials('newowner@example.com', 'password123')">
                                Fill
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <footer class="footer" style="margin-top:auto;">
        <div>© {{ date('Y') }} LIVING HOPE Wi-Fi Cloud. All rights reserved.</div>
        <div style="display:flex;gap:16px;">
            <a href="{{ route('portal.index') }}" target="_blank">Captive Portal</a>
            <a href="{{ route('register') }}">Register</a>
            <a href="#">Support</a>
        </div>
    </footer>

    <script>
        // Fill credentials into form with a flash highlight
        function fillCredentials(email, password) {
            const emailInput = document.getElementById('emailInput');
            const passInput = document.getElementById('passInput');
            
            emailInput.value = email;
            passInput.value = password;

            emailInput.classList.remove('field-flash');
            passInput.classList.remove('field-flash');
            
            // Trigger reflow to restart animation
            void emailInput.offsetWidth;
            void passInput.offsetWidth;

            emailInput.classList.add('field-flash');
            passInput.classList.add('field-flash');

            // Scroll to form smoothly on mobile
            if (window.innerWidth < 980) {
                document.getElementById('mainLoginCard').scrollIntoView({ behavior: 'smooth' });
            }
        }

        // Toggle Password Show / Hide
        const toggleBtn = document.getElementById('togglePassBtn');
        const passInput = document.getElementById('passInput');
        if (toggleBtn && passInput) {
            toggleBtn.addEventListener('click', function() {
                if (passInput.type === 'password') {
                    passInput.type = 'text';
                    toggleBtn.textContent = '🙈';
                    toggleBtn.title = 'Hide password';
                } else {
                    passInput.type = 'password';
                    toggleBtn.textContent = '👁️';
                    toggleBtn.title = 'Show password';
                }
            });
        }
    </script>
</body>
</html>
