<?php

namespace App\Http\Controllers;

use App\Models\ActiveSession;
use App\Models\Activity;
use App\Models\Package;
use App\Models\Voucher;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VoucherController extends Controller
{
    /**
     * Display a listing of the vouchers.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $ownerId = $user->getEffectiveUserId();

        $search = $request->query('search');
        $status = $request->query('status');
        $packageId = $request->query('package_id');

        $query = Voucher::with('package')->latest('id');

        // Scoping
        if (! $user->isAdmin() || ! $request->has('global')) {
            $query->where('user_id', $ownerId);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                    ->orWhere('customer_phone', 'like', "%{$search}%")
                    ->orWhere('customer_name', 'like', "%{$search}%")
                    ->orWhere('batch_id', 'like', "%{$search}%");
            });
        }

        if ($status && in_array($status, ['available', 'active', 'expired', 'revoked'])) {
            $query->where('status', $status);
        }

        if ($packageId) {
            $query->where('package_id', $packageId);
        }

        $vouchers = $query->paginate(15)->withQueryString();

        $packagesQuery = Package::where('is_active', true);
        if (! $user->isAdmin() || ! $request->has('global')) {
            $packagesQuery->where('user_id', $ownerId);
        }
        $packages = $packagesQuery->get();

        $statsBase = Voucher::query();
        if (! $user->isAdmin() || ! $request->has('global')) {
            $statsBase->where('user_id', $ownerId);
        }

        $stats = [
            'total' => (clone $statsBase)->count(),
            'available' => (clone $statsBase)->where('status', 'available')->count(),
            'active' => (clone $statsBase)->where('status', 'active')->count(),
            'expired' => (clone $statsBase)->where('status', 'expired')->count(),
            'revoked' => (clone $statsBase)->where('status', 'revoked')->count(),
        ];

        return view('vouchers.index', compact('vouchers', 'packages', 'stats'));
    }

    /**
     * Sell a voucher (Instant sale to customer or walk-in).
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        $ownerId = $user->getEffectiveUserId();
        $effectiveUser = $user->getEffectiveUser();

        $validated = $request->validate([
            'package_id' => 'required|exists:packages,id',
            'customer_name' => 'nullable|string|max:100',
            'customer_phone' => 'nullable|string|max:30',
            'cashier' => 'nullable|string|max:60',
            'auto_activate' => 'nullable|boolean',
        ]);

        $package = Package::where('id', $validated['package_id'])
            ->where('user_id', $ownerId)
            ->firstOrFail();

        $autoActivate = $request->boolean('auto_activate');
        $codePrefix = $effectiveUser->portal_logo_text ?: 'LH';

        $voucher = new Voucher;
        $voucher->user_id = $ownerId;
        $voucher->code = Voucher::generateCode($codePrefix);
        $voucher->package_id = $package->id;
        $voucher->amount = $package->price;
        $voucher->customer_name = $validated['customer_name'] ?? 'Walk-in customer';
        $voucher->customer_phone = $validated['customer_phone'];
        $voucher->cashier = $validated['cashier'] ?: $user->name;

        if ($autoActivate) {
            $voucher->status = 'active';
            $voucher->activated_at = Carbon::now();
            $voucher->expires_at = Carbon::now()->addHours($package->duration_hours);
            $voucher->mac_address = sprintf('%02X:%02X:%02X:%02X:%02X:%02X', rand(0, 255), rand(0, 255), rand(0, 255), rand(0, 255), rand(0, 255), rand(0, 255));
        } else {
            $voucher->status = 'available';
        }

        $voucher->save();

        if ($autoActivate) {
            ActiveSession::create([
                'voucher_id' => $voucher->id,
                'ip_address' => '192.168.0.'.rand(100, 240),
                'mac_address' => $voucher->mac_address,
                'device_name' => 'Customer Handset',
                'connected_at' => Carbon::now(),
                'status' => 'online',
            ]);
        }

        Activity::create([
            'user_id' => $ownerId,
            'icon' => '＋',
            'title' => 'Voucher sale recorded',
            'description' => "{$package->name} · {$package->formatted_price} · Sold by {$voucher->cashier}",
            'type' => 'sale',
        ]);

        return back()->with('success', "Voucher {$voucher->code} created successfully for {$voucher->customer_name}!");
    }

    /**
     * Generate bulk batch of vouchers for printing.
     */
    public function generateBatch(Request $request)
    {
        $user = Auth::user();
        $ownerId = $user->getEffectiveUserId();
        $effectiveUser = $user->getEffectiveUser();

        $validated = $request->validate([
            'package_id' => 'required|exists:packages,id',
            'quantity' => 'required|integer|min:1|max:500',
            'batch_tag' => 'nullable|string|max:50',
        ]);

        $package = Package::where('id', $validated['package_id'])
            ->where('user_id', $ownerId)
            ->firstOrFail();

        $qty = $validated['quantity'];
        $batchId = 'BATCH-'.date('Ymd-Hi').'-'.strtoupper(substr(md5(uniqid()), 0, 4));
        if (! empty($validated['batch_tag'])) {
            $batchId = strtoupper(trim($validated['batch_tag'])).'-'.date('Ymd-Hi');
        }

        $codePrefix = $effectiveUser->portal_logo_text ?: 'LH';

        for ($i = 0; $i < $qty; $i++) {
            Voucher::create([
                'user_id' => $ownerId,
                'code' => Voucher::generateCode($codePrefix),
                'package_id' => $package->id,
                'amount' => $package->price,
                'status' => 'available',
                'batch_id' => $batchId,
                'cashier' => $user->name,
            ]);
        }

        Activity::create([
            'user_id' => $ownerId,
            'icon' => '▤',
            'title' => 'Voucher batch generated',
            'description' => "{$qty} vouchers ({$package->name}) generated under {$batchId}",
            'type' => 'batch',
        ]);

        return back()->with('success', "Successfully generated {$qty} vouchers under Batch: {$batchId}!");
    }

    /**
     * Revoke an active or available voucher.
     */
    public function revoke($id)
    {
        $user = Auth::user();
        $ownerId = $user->getEffectiveUserId();

        $query = Voucher::where('id', $id);
        if (! $user->isAdmin()) {
            $query->where('user_id', $ownerId);
        }

        $voucher = $query->firstOrFail();
        $voucher->status = 'revoked';
        $voucher->notes = 'Revoked by '.$user->name.' on '.Carbon::now()->toDateTimeString();
        $voucher->save();

        if ($voucher->activeSession) {
            $voucher->activeSession->status = 'terminated';
            $voucher->activeSession->save();
        }

        Activity::create([
            'user_id' => $ownerId,
            'icon' => '✕',
            'title' => 'Voucher revoked',
            'description' => "Code {$voucher->code} was revoked and session disconnected",
            'type' => 'revoke',
        ]);

        return back()->with('success', "Voucher {$voucher->code} was revoked successfully.");
    }

    /**
     * Print single voucher ticket.
     */
    public function printTicket($id)
    {
        $user = Auth::user();
        $ownerId = $user->getEffectiveUserId();

        $query = Voucher::with('package')->where('id', $id);
        if (! $user->isAdmin()) {
            $query->where('user_id', $ownerId);
        }

        $voucher = $query->firstOrFail();
        $host = $voucher->user ?: $user->getEffectiveUser();

        return view('vouchers.print', compact('voucher', 'host'));
    }
}
