<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\Package;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PackageController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $ownerId = $user->getEffectiveUserId();

        $query = Package::withCount('vouchers');
        if (! $user->isAdmin() || ! $request->has('global')) {
            $query->where('user_id', $ownerId);
        }

        $packages = $query->get();

        return view('packages.index', compact('packages'));
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        $ownerId = $user->getEffectiveUserId();

        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'duration_hours' => 'required|integer|min:1',
            'price' => 'required|integer|min:100',
            'speed_limit' => 'nullable|string|max:100',
            'device_limit' => 'nullable|integer|min:1|max:10',
            'badge_label' => 'nullable|string|max:50',
            'icon' => 'nullable|string|max:10',
            'description' => 'nullable|string',
        ]);

        $package = Package::create([
            'user_id' => $ownerId,
            'name' => $validated['name'],
            'duration_hours' => $validated['duration_hours'],
            'price' => $validated['price'],
            'speed_limit' => $validated['speed_limit'] ?? '5 Mbps / 2 Mbps',
            'device_limit' => $validated['device_limit'] ?? 1,
            'badge_label' => strtoupper($validated['badge_label'] ?? 'PASS'),
            'icon' => $validated['icon'] ?? '◈',
            'description' => $validated['description'] ?? 'Wi-Fi access package.',
            'is_active' => true,
        ]);

        Activity::create([
            'user_id' => $ownerId,
            'icon' => '◈',
            'title' => 'New Wi-Fi package created',
            'description' => "Package {$package->name} ({$package->formatted_price}) added to hotspot",
            'type' => 'package',
        ]);

        return redirect()->route('packages.index')->with('success', "Package '{$package->name}' created successfully!");
    }

    public function update(Request $request, $id)
    {
        $user = Auth::user();
        $ownerId = $user->getEffectiveUserId();

        $query = Package::where('id', $id);
        if (! $user->isAdmin()) {
            $query->where('user_id', $ownerId);
        }

        $package = $query->firstOrFail();

        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'price' => 'required|integer|min:100',
            'speed_limit' => 'nullable|string|max:100',
            'badge_label' => 'nullable|string|max:50',
            'description' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ]);

        $package->update($validated);

        return redirect()->route('packages.index')->with('success', "Package '{$package->name}' updated successfully!");
    }
}
