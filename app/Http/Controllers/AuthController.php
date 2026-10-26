<?php

namespace App\Http\Controllers;

use App\Models\Package;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    /**
     * Show login form.
     */
    public function showLogin()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        return view('auth.login');
    }

    /**
     * Process login request.
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $remember = $request->boolean('remember');

        if (Auth::attempt($credentials, $remember)) {
            $request->session()->regenerate();

            $user = Auth::user();
            if (! $user->isVerified()) {
                return redirect()->route('verification.notice')->with('warning', 'Please verify your email address to activate your Wi-Fi hotspot.');
            }

            return redirect()->intended(route('dashboard'))->with('success', "Welcome back, {$user->name}!");
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    /**
     * Show registration form.
     */
    public function showRegister()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        return view('auth.register');
    }

    /**
     * Handle registration of a new Wi-Fi Hotspot Owner.
     */
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'business_name' => 'required|string|max:255',
            'portal_ssid' => 'nullable|string|max:64',
            'phone' => 'nullable|string|max:30',
            'password' => 'required|string|min:6|confirmed',
        ]);

        // Generate clean unique portal slug
        $baseSlug = Str::slug($validated['business_name']);
        $slug = $baseSlug ?: 'hotspot';
        $counter = 1;
        while (User::where('portal_slug', $slug)->exists()) {
            $slug = "{$baseSlug}-{$counter}";
            $counter++;
        }

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => 'vendor',
            'business_name' => $validated['business_name'],
            'portal_slug' => $slug,
            'phone' => $validated['phone'] ?? null,
            'currency' => 'TZS',
            'portal_title' => strtoupper($validated['business_name']).' WI-FI',
            'portal_welcome_text' => 'Welcome to '.$validated['business_name'].'. Enter your voucher code below to connect to high-speed internet.',
            'portal_theme_color' => '#2563eb',
            'portal_logo_text' => strtoupper(substr($validated['business_name'], 0, 2)),
            'portal_ssid' => $validated['portal_ssid'] ?: strtoupper(Str::snake($validated['business_name'], '_')).'_WIFI',
            'email_verified_at' => null, // Requires verification as requested
        ]);

        // Create default starter packages for this new Wi-Fi hotspot owner
        Package::create([
            'user_id' => $user->id,
            'name' => '1 Hour Pass',
            'duration_hours' => 1,
            'price' => 500,
            'speed_limit' => '5 Mbps / 2 Mbps',
            'device_limit' => 1,
            'badge_label' => 'STARTER',
            'icon' => '◷',
            'description' => 'Fast internet access for up to 1 hour.',
            'is_active' => true,
        ]);

        Package::create([
            'user_id' => $user->id,
            'name' => '24 Hours Pass',
            'duration_hours' => 24,
            'price' => 1500,
            'speed_limit' => '10 Mbps / 3 Mbps',
            'device_limit' => 1,
            'badge_label' => 'DAILY PASS',
            'icon' => '☼',
            'description' => 'Full day unlimited internet connection.',
            'is_active' => true,
        ]);

        Package::create([
            'user_id' => $user->id,
            'name' => '7 Days Pass',
            'duration_hours' => 168,
            'price' => 7000,
            'speed_limit' => '15 Mbps / 5 Mbps',
            'device_limit' => 2,
            'badge_label' => 'WEEKLY PASS',
            'icon' => '◈',
            'description' => 'Weekly unlimited connectivity for work and browsing.',
            'is_active' => true,
        ]);

        Auth::login($user);

        return redirect()->route('verification.notice')->with('success', 'Account created! Please verify your email to unlock your hotspot portal.');
    }

    /**
     * Show email verification notice page.
     */
    public function showVerifyEmail()
    {
        $user = Auth::user();
        if (! $user) {
            return redirect()->route('login');
        }

        if ($user->isVerified()) {
            return redirect()->route('dashboard');
        }

        return view('auth.verify-email', compact('user'));
    }

    /**
     * Complete email verification.
     */
    public function verifyEmail(Request $request)
    {
        $user = Auth::user();
        if (! $user) {
            return redirect()->route('login');
        }

        $user->email_verified_at = now();
        $user->save();

        return redirect()->route('dashboard')->with('success', 'Your email has been verified! Your Wi-Fi hotspot and captive portal are now active.');
    }

    /**
     * Log user out.
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('info', 'You have been logged out.');
    }

    /**
     * Quick demo switch for easy testing during review.
     */
    public function demoLogin(string $type)
    {
        $email = match ($type) {
            'admin' => 'admin@livinghope.co.tz',
            'vendor' => 'vendor@cafe.co.tz',
            'cashier' => 'cashier@livinghope.co.tz',
            'unverified' => 'newowner@example.com',
            default => 'admin@livinghope.co.tz',
        };

        $user = User::where('email', $email)->first();
        if ($user) {
            Auth::login($user);
            request()->session()->regenerate();

            if (! $user->isVerified()) {
                return redirect()->route('verification.notice');
            }

            return redirect()->route('dashboard')->with('success', "Logged in as {$user->name} ({$user->role})");
        }

        return redirect()->route('login')->with('error', 'Demo account not found.');
    }
}
