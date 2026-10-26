<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class StaffController extends Controller
{
    /**
     * Display staff and permissions.
     */
    public function index()
    {
        $user = Auth::user();
        $ownerId = $user->getEffectiveUserId();

        // If admin, show all staff or scoped staff
        if ($user->isAdmin()) {
            $staff = User::where('role', 'staff')->orWhereNotNull('parent_id')->with('parent')->latest()->get();
        } else {
            $staff = User::where('parent_id', $ownerId)->latest()->get();
        }

        return view('staff.index', compact('staff'));
    }

    /**
     * Store a newly created staff member.
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        $ownerId = $user->getEffectiveUserId();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'nullable|string|max:30',
            'role_title' => 'required|string|in:cashier,operator,manager',
            'password' => 'required|string|min:6',
        ]);

        $staff = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'password' => Hash::make($validated['password']),
            'role' => 'staff',
            'business_name' => $user->business_name,
            'parent_id' => $ownerId,
            'email_verified_at' => now(), // created by verified owner
        ]);

        Activity::create([
            'user_id' => $ownerId,
            'icon' => '♧',
            'title' => 'New staff member added',
            'description' => "{$staff->name} assigned as ".ucfirst($validated['role_title']),
            'type' => 'staff',
        ]);

        return back()->with('success', "Staff member {$staff->name} has been added successfully.");
    }

    /**
     * Remove or revoke a staff member.
     */
    public function destroy($id)
    {
        $user = Auth::user();
        $ownerId = $user->getEffectiveUserId();

        $query = User::where('id', $id)->where('role', 'staff');
        if (! $user->isAdmin()) {
            $query->where('parent_id', $ownerId);
        }

        $staff = $query->firstOrFail();
        $staffName = $staff->name;
        $staff->delete();

        Activity::create([
            'user_id' => $ownerId,
            'icon' => '✕',
            'title' => 'Staff account removed',
            'description' => "Account for {$staffName} was removed",
            'type' => 'staff',
        ]);

        return back()->with('success', "Staff account {$staffName} removed.");
    }
}
