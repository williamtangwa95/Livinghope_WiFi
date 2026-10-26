<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#0b1220">
    <title>Verify Email | LIVING HOPE Wi-Fi Cloud Platform</title>
    <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
    <style>
        body {
            background: linear-gradient(135deg, #070d18 0%, #0f1c30 50%, #091322 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px 16px;
            font-family: system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            color: #f1f5f9;
        }

        .verify-container {
            width: 100%;
            max-width: 520px;
            background: rgba(15, 23, 42, 0.8);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.12);
            border-radius: 24px;
            box-shadow: 0 25px 60px -15px rgba(0, 0, 0, 0.6), 0 0 40px rgba(245, 158, 11, 0.15);
            padding: 40px 32px;
            text-align: center;
            position: relative;
        }

        .verify-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #f59e0b, #fbbf24, #10b981);
        }

        .icon-circle {
            width: 72px;
            height: 72px;
            border-radius: 22px;
            background: rgba(245, 158, 11, 0.15);
            border: 1px solid rgba(245, 158, 11, 0.3);
            color: #fbbf24;
            font-size: 32px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 20px;
            box-shadow: 0 10px 25px rgba(245, 158, 11, 0.2);
        }

        .verify-title {
            font-size: 24px;
            font-weight: 700;
            color: #ffffff;
            margin: 0 0 8px 0;
        }

        .verify-desc {
            font-size: 14px;
            color: #94a3b8;
            line-height: 1.6;
            margin: 0 0 24px 0;
        }

        .user-badge {
            background: rgba(30, 41, 59, 0.8);
            border: 1px solid rgba(255, 255, 255, 0.1);
            padding: 12px 16px;
            border-radius: 14px;
            font-size: 13px;
            color: #cbd5e1;
            margin-bottom: 28px;
            display: inline-block;
        }

        .features-list {
            text-align: left;
            background: rgba(15, 23, 42, 0.6);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 14px;
            padding: 16px 20px;
            margin-bottom: 28px;
        }

        .features-list h4 {
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #38bdf8;
            margin: 0 0 10px 0;
        }

        .features-list ul {
            list-style: none;
            padding: 0;
            margin: 0;
            font-size: 13px;
            color: #cbd5e1;
        }

        .features-list li {
            padding: 4px 0;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .btn-verify {
            width: 100%;
            background: linear-gradient(135deg, #10b981, #059669);
            color: white;
            padding: 14px;
            border: none;
            border-radius: 12px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            box-shadow: 0 10px 25px rgba(16, 185, 129, 0.35);
            transition: all 0.2s ease;
        }

        .btn-verify:hover {
            transform: translateY(-1px);
            box-shadow: 0 14px 28px rgba(16, 185, 129, 0.45);
        }

        .btn-logout {
            background: none;
            border: none;
            color: #94a3b8;
            font-size: 13px;
            cursor: pointer;
            margin-top: 16px;
            text-decoration: underline;
        }

        .btn-logout:hover {
            color: #f1f5f9;
        }
    </style>
</head>
<body>
    <div class="verify-container">
        <div class="icon-circle">✉</div>
        <h1 class="verify-title">Verify Your Hotspot Account</h1>
        <p class="verify-desc">
            Thanks for registering <strong>{{ $user->business_name }}</strong>!
            To protect network integrity, please verify your email address to unlock your management dashboard and activate your captive portal.
        </p>

        <div class="user-badge">
            Registered as: <strong>{{ $user->name }}</strong> ({{ $user->email }})
        </div>

        <div class="features-list">
            <h4>Once verified, you can immediately manage:</h4>
            <ul>
                <li>✓ <strong>Voucher Management:</strong> Generate single & batch codes with instant printing</li>
                <li>✓ <strong>Wi-Fi Packages:</strong> Custom pricing, durations, speeds, and limits</li>
                <li>✓ <strong>Customer Portal:</strong> Own your custom captive portal (`/portal/{{ $user->portal_slug }}`)</li>
                <li>✓ <strong>Reports & Analytics:</strong> Revenue charts, daily totals, and CSV transactions</li>
                <li>✓ <strong>Staff & Permissions:</strong> Create cashier logins for your venue attendants</li>
                <li>✓ <strong>Settings:</strong> SSID, gateway integration, and portal branding</li>
            </ul>
        </div>

        <form action="{{ route('verification.verify') }}" method="POST">
            @csrf
            <button type="submit" class="btn-verify">
                ✓ Verify Email Address & Enter Workspace ➔
            </button>
        </form>

        <form action="{{ route('logout') }}" method="POST">
            @csrf
            <button type="submit" class="btn-logout">
                Sign Out & Switch Account
            </button>
        </form>
    </div>
</body>
</html>
