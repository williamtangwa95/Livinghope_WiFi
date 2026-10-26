<?php

namespace App\Http\Controllers;

use App\Contracts\HotspotGatewayInterface;
use App\Models\ActiveSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SessionController extends Controller
{
    public function __construct(
        protected HotspotGatewayInterface $gateway
    ) {}

    public function index(Request $request)
    {
        $user = Auth::user();
        $ownerId = $user->getEffectiveUserId();

        // Automatically sweep and mark expired sessions
        $this->gateway->syncExpiredSessions();

        $query = ActiveSession::with(['voucher.package', 'voucher.devices'])->latest('connected_at');
        if (! $user->isAdmin() || ! $request->has('global')) {
            $query->whereHas('voucher', function ($q) use ($ownerId) {
                $q->where('user_id', $ownerId);
            });
        }

        $sessions = $query->get();
        $gatewayInfo = [
            'driver' => $this->gateway->getDriverName(),
            'is_available' => $this->gateway->isAvailable(),
            'mock_mode' => (bool) config('hotspot.mock_mode', true),
        ];

        return view('sessions.index', compact('sessions', 'gatewayInfo'));
    }

    public function disconnect($id)
    {
        $user = Auth::user();
        $ownerId = $user->getEffectiveUserId();

        $query = ActiveSession::with('voucher')->where('id', $id);
        if (! $user->isAdmin()) {
            $query->whereHas('voucher', function ($q) use ($ownerId) {
                $q->where('user_id', $ownerId);
            });
        }

        $session = $query->firstOrFail();
        $code = $session->voucher->code ?? 'Unknown';

        // Disconnect through gateway abstraction
        $this->gateway->disconnectSession($session);

        return redirect()->back()->with('success', "Simulated session for voucher {$code} has been disconnected.");
    }

    /**
     * Trigger simulated session expiry check.
     */
    public function syncExpired()
    {
        $expired = $this->gateway->syncExpiredSessions();

        return redirect()->back()->with('success', "Simulated gateway sync completed. {$expired} expired session(s) cleaned up.");
    }
}
