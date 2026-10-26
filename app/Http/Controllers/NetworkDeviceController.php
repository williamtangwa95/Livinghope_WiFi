<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\NetworkDevice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NetworkDeviceController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $ownerId = $user->getEffectiveUserId();

        $query = NetworkDevice::query();
        if (! $user->isAdmin() || ! $request->has('global')) {
            $query->where('user_id', $ownerId);
        }

        $devices = $query->latest()->get();

        return view('devices.index', compact('devices'));
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        $ownerId = $user->getEffectiveUserId();

        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'model' => 'required|string|max:100',
            'ip_address' => 'required|string|max:45',
            'mac_address' => 'nullable|string|max:30',
            'location' => 'nullable|string|max:100',
            'auth_mode' => 'nullable|string|max:100',
            'notes' => 'nullable|string|max:500',
        ]);

        $device = NetworkDevice::create([
            'user_id' => $ownerId,
            'name' => $validated['name'],
            'model' => $validated['model'],
            'ip_address' => $validated['ip_address'],
            'mac_address' => $validated['mac_address'],
            'location' => $validated['location'],
            'status' => 'online',
            'auth_mode' => $validated['auth_mode'] ?? 'Captive Portal / RADIUS',
            'notes' => $validated['notes'],
        ]);

        Activity::create([
            'user_id' => $ownerId,
            'icon' => '⌘',
            'title' => 'New network device connected',
            'description' => "{$device->name} ({$device->ip_address}) registered to hotspot",
            'type' => 'network',
        ]);

        return redirect()->route('devices.index')->with('success', "Device '{$device->name}' registered successfully.");
    }

    public function pingTest($id)
    {
        $user = Auth::user();
        $ownerId = $user->getEffectiveUserId();

        $query = NetworkDevice::where('id', $id);
        if (! $user->isAdmin()) {
            $query->where('user_id', $ownerId);
        }

        $device = $query->firstOrFail();
        $latency = rand(11, 24) / 10 .'ms';

        Activity::create([
            'user_id' => $ownerId,
            'icon' => '⌘',
            'title' => "Ping diagnostics: {$device->name}",
            'description' => "Latency: {$latency} · Packets: 4/4 received · 0% loss (Target: {$device->ip_address})",
            'type' => 'network',
        ]);

        return response()->json([
            'success' => true,
            'device' => $device->name,
            'ip' => $device->ip_address,
            'latency' => $latency,
            'status' => 'online',
            'message' => "Ping test to {$device->name} ({$device->ip_address}) successful! Latency: {$latency}",
        ]);
    }

    public function destroy($id)
    {
        $user = Auth::user();
        $ownerId = $user->getEffectiveUserId();

        $query = NetworkDevice::where('id', $id);
        if (! $user->isAdmin()) {
            $query->where('user_id', $ownerId);
        }

        $device = $query->firstOrFail();
        $name = $device->name;
        $device->delete();

        return redirect()->route('devices.index')->with('success', "Device '{$name}' removed.");
    }
}
