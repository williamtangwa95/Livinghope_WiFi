<?php

namespace App\Http\Controllers;

use App\Models\ActiveSession;
use App\Models\Package;
use App\Models\Voucher;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReportsController extends Controller
{
    /**
     * Display Reports and Analytics.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $ownerId = $user->getEffectiveUserId();

        $queryUser = $user->isAdmin() && $request->has('all') ? null : $ownerId;

        // Date range filter
        $range = $request->query('range', '7days');
        $startDate = match ($range) {
            'today' => Carbon::today(),
            '30days' => Carbon::now()->subDays(30),
            'thismonth' => Carbon::now()->startOfMonth(),
            default => Carbon::now()->subDays(7),
        };

        // Base voucher query
        $vouchersQuery = Voucher::query();
        if ($queryUser) {
            $vouchersQuery->where('user_id', $queryUser);
        }

        $totalRevenue = (clone $vouchersQuery)
            ->whereIn('status', ['active', 'expired', 'available'])
            ->where('created_at', '>=', $startDate)
            ->sum('amount');

        $totalSold = (clone $vouchersQuery)
            ->whereIn('status', ['active', 'expired'])
            ->where('created_at', '>=', $startDate)
            ->count();

        $activeVouchersCount = (clone $vouchersQuery)->where('status', 'active')->count();
        $availableCount = (clone $vouchersQuery)->where('status', 'available')->count();
        $expiredCount = (clone $vouchersQuery)->where('status', 'expired')->count();
        $revokedCount = (clone $vouchersQuery)->where('status', 'revoked')->count();

        // Packages breakdown
        $packagesQuery = Package::query();
        if ($queryUser) {
            $packagesQuery->where('user_id', $queryUser);
        }

        $packages = $packagesQuery->withCount(['vouchers as sold_count' => function ($q) use ($startDate) {
            $q->whereIn('status', ['active', 'expired'])->where('created_at', '>=', $startDate);
        }])->get();

        // Daily breakdown for the chart
        $dailyData = [];
        $daysCount = $range === '30days' ? 30 : 7;
        for ($i = $daysCount - 1; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $dayLabel = $date->format($daysCount > 7 ? 'M d' : 'D, M d');

            $dayRev = (clone $vouchersQuery)
                ->whereDate('created_at', $date)
                ->whereIn('status', ['active', 'expired', 'available'])
                ->sum('amount');

            $daySold = (clone $vouchersQuery)
                ->whereDate('created_at', $date)
                ->whereIn('status', ['active', 'expired'])
                ->count();

            $dailyData[] = [
                'date' => $dayLabel,
                'revenue' => $dayRev,
                'count' => $daySold,
            ];
        }

        // Active sessions query
        $sessionsQuery = ActiveSession::query();
        if ($queryUser) {
            $sessionsQuery->whereHas('voucher', function ($q) use ($queryUser) {
                $q->where('user_id', $queryUser);
            });
        }
        $totalOnlineClients = $sessionsQuery->where('status', 'online')->count();
        $totalBytesDown = $sessionsQuery->sum('bytes_down');
        $totalBytesUp = $sessionsQuery->sum('bytes_up');

        return view('reports.index', compact(
            'totalRevenue',
            'totalSold',
            'activeVouchersCount',
            'availableCount',
            'expiredCount',
            'revokedCount',
            'packages',
            'dailyData',
            'totalOnlineClients',
            'totalBytesDown',
            'totalBytesUp',
            'range'
        ));
    }
}
