<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#0b1220">
    <title>Register Hotspot Owner | LIVING HOPE Wi-Fi Cloud Platform</title>
    <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
    <style>
        body {
            background: linear-gradient(135deg, #070d18 0%, #0f1c30 50%, #091322 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 32px 16px;
            font-family: system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            color: #f1f5f9;
        }

        .auth-container {
            width: 100%;
            max-width: 520px;
            background: rgba(15, 23, 42, 0.75);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.12);
            border-radius: 24px;
            box-shadow: 0 25px 60px -15px rgba(0, 0, 0, 0.6), 0 0 40px rgba(37, 99, 235, 0.15);
            padding: 36px 32px;
            position: relative;
        }

        .auth-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #10b981, #38bdf8, #2563eb);
        }

        .auth-header {
            text-align: center;
            margin-bottom: 24px;
        }

        .auth-logo {
            width: 52px;
            height: 52px;
            border-radius: 14px;
            background: linear-gradient(135deg, #059669, #0284c7);
            color: white;
            font-weight: 800;
            font-size: 20px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 10px 25px rgba(16, 185, 129, 0.35);
            margin-bottom: 12px;
        }

        .auth-title {
            font-size: 22px;
            font-weight: 700;
            color: #ffffff;
            margin: 0 0 6px 0;
            letter-spacing: -0.02em;
        }

        .auth-subtitle {
            font-size: 13px;
            color: #94a3b8;
            margin: 0;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 14px;
        }

        @media (max-width: 480px) {
            .form-row {
                grid-template-columns: 1fr;
            }
        }

        .auth-form .form-group {
            margin-bottom: 16px;
        }

        .auth-form label {
            display: block;
            font-size: 11px;
            font-weight: 600;
            color: #cbd5e1;
            margin-bottom: 6px;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }

        .auth-input {
            width: 100%;
            box-sizing: border-box;
            background: rgba(30, 41, 59, 0.7);
            border: 1px solid rgba(255, 255, 255, 0.12);
            color: #ffffff;
            padding: 11px 13px;
            border-radius: 12px;
            font-size: 13px;
            outline: none;
            transition: all 0.2s ease;
        }

        .auth-input:focus {
            border-color: #38bdf8;
            background: rgba(30, 41, 59, 0.95);
            box-shadow: 0 0 0 3px rgba(56, 189, 248, 0.2);
        }

        .auth-btn-submit {
            width: 100%;
            background: linear-gradient(135deg, #059669, #0284c7);
            color: white;
            padding: 13px;
            border: none;
            border-radius: 12px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            box-shadow: 0 8px 20px rgba(16, 185, 129, 0.35);
            transition: all 0.2s ease;
            margin-top: 10px;
        }

        .auth-btn-submit:hover {
            transform: translateY(-1px);
            box-shadow: 0 12px 24px rgba(16, 185, 129, 0.45);
        }

        .auth-footer {
            margin-top: 22px;
            text-align: center;
            font-size: 13px;
            color: #94a3b8;
        }

        .auth-footer a {
            color: #38bdf8;
            text-decoration: none;
            font-weight: 600;
        }

        .feature-tag {
            background: rgba(56, 189, 248, 0.1);
            border: 1px solid rgba(56, 189, 248, 0.2);
            color: #7dd3fc;
            padding: 6px 10px;
            border-radius: 8px;
            font-size: 11px;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            margin-bottom: 16px;
        }

        .alert-error {
            background: rgba(239, 68, 68, 0.15);
            border: 1px solid rgba(239, 68, 68, 0.3);
            color: #fca5a5;
            padding: 12px 14px;
            border-radius: 12px;
            font-size: 13px;
            margin-bottom: 18px;
        }
    </style>
</head>
<body>
    <div class="auth-container">
        <div class="auth-header">
            <div class="auth-logo">📶</div>
            <h1 class="auth-title">Register Wi-Fi Hotspot</h1>
            <p class="auth-subtitle">Create your business account & own your branded captive portal</p>
        </div>

        <div style="text-align:center;">
            <div class="feature-tag">
                <span>✦</span> Each registered user receives their own independent Captive Portal URL
            </div>
        </div>

        @if($errors->any())
            <div class="alert-error">
                ⚠️ {{ $errors->first() }}
            </div>
        @endif

        <form action="{{ route('register') }}" method="POST" class="auth-form">
            @csrf
            
            <div class="form-row">
                <div class="form-group">
                    <label for="name">Your Full Name *</label>
                    <input type="text" id="name" name="name" value="{{ old('name') }}" class="auth-input" placeholder="e.g. Salim Rashid" required>
                </div>

                <div class="form-group">
                    <label for="business_name">Business / Hotspot Name *</label>
                    <input type="text" id="business_name" name="business_name" value="{{ old('business_name') }}" class="auth-input" placeholder="e.g. Serengeti Garden Lodge" required>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="portal_ssid">Broadcast Wi-Fi SSID</label>
                    <input type="text" id="portal_ssid" name="portal_ssid" value="{{ old('portal_ssid') }}" class="auth-input" placeholder="e.g. SERENGETI_FREE_WIFI">
                </div>

                <div class="form-group">
                    <label for="phone">Phone / WhatsApp</label>
                    <input type="text" id="phone" name="phone" value="{{ old('phone') }}" class="auth-input" placeholder="+255 712 345 678">
                </div>
            </div>

            <div class="form-group">
                <label for="email">Email Address (Used to Login) *</label>
                <input type="email" id="email" name="email" value="{{ old('email') }}" class="auth-input" placeholder="owner@serengeti.co.tz" required>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="password">Password (min 6 chars) *</label>
                    <input type="password" id="password" name="password" class="auth-input" placeholder="••••••••" required>
                </div>

                <div class="form-group">
                    <label for="password_confirmation">Confirm Password *</label>
                    <input type="password" id="password_confirmation" name="password_confirmation" class="auth-input" placeholder="••••••••" required>
                </div>
            </div>

            <button type="submit" class="auth-btn-submit">
                Create Account & Generate Captive Portal ➔
            </button>
        </form>

        <div class="auth-footer">
            Already have an account?
            <a href="{{ route('login') }}">Sign In to Workspace</a>
        </div>
    </div>
</body>
</html>
