<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class SettingsController extends Controller
{
    /**
     * Display settings and portal customization.
     */
    public function index()
    {
        $user = Auth::user()->getEffectiveUser();

        return view('settings.index', compact('user'));
    }

    /**
     * Update hotspot and captive portal settings.
     */
    public function update(Request $request)
    {
        $user = Auth::user()->getEffectiveUser();

        $validated = $request->validate([
            'business_name' => 'required|string|max:255',
            'portal_slug' => 'required|string|max:100|alpha_dash|unique:users,portal_slug,'.$user->id,
            'portal_ssid' => 'required|string|max:64',
            'phone' => 'nullable|string|max:30',
            'currency' => 'required|string|max:10',
            'portal_title' => 'required|string|max:255',
            'portal_welcome_text' => 'nullable|string|max:1000',
            'portal_theme_color' => 'required|string|max:20',
            'portal_logo_text' => 'required|string|max:6',
        ]);

        $user->update([
            'business_name' => $validated['business_name'],
            'portal_slug' => Str::slug($validated['portal_slug']),
            'portal_ssid' => $validated['portal_ssid'],
            'phone' => $validated['phone'],
            'currency' => strtoupper($validated['currency']),
            'portal_title' => $validated['portal_title'],
            'portal_welcome_text' => $validated['portal_welcome_text'],
            'portal_theme_color' => $validated['portal_theme_color'],
            'portal_logo_text' => strtoupper($validated['portal_logo_text']),
        ]);

        Activity::create([
            'user_id' => $user->id,
            'icon' => '⚙',
            'title' => 'Hotspot & Portal settings updated',
            'description' => "Captive portal brand and SSID ({$user->portal_ssid}) saved",
            'type' => 'settings',
        ]);

        return back()->with('success', 'Your Wi-Fi hotspot and captive portal settings have been updated successfully.');
    }
}
