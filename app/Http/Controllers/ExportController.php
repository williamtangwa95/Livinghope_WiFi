<?php

namespace App\Http\Controllers;

use App\Models\Voucher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportController extends Controller
{
    /**
     * Export vouchers as CSV.
     */
    public function vouchers(Request $request): StreamedResponse
    {
        $user = Auth::user();
        $ownerId = $user->getEffectiveUserId();
        $business = $user->business_name ?: 'wifi';
        $fileName = 'vouchers-'.Str::slug($business).'-'.date('Y-m-d_His').'.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        return response()->stream(function () use ($user, $ownerId, $request) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Voucher Code', 'Package', 'Amount', 'Customer Name', 'Customer Phone', 'Status', 'Cashier', 'Batch ID', 'Activated At', 'Expires At', 'Created At']);

            $query = Voucher::with('package')->latest('id');
            if (! $user->isAdmin() || ! $request->has('global')) {
                $query->where('user_id', $ownerId);
            }

            $query->chunk(200, function ($vouchers) use ($handle) {
                foreach ($vouchers as $v) {
                    fputcsv($handle, [
                        $v->code,
                        $v->package->name ?? 'N/A',
                        $v->amount,
                        $v->customer_name ?? 'Walk-in',
                        $v->customer_phone ?? 'N/A',
                        ucfirst($v->status),
                        $v->cashier ?? 'N/A',
                        $v->batch_id ?? 'Single',
                        $v->activated_at ? $v->activated_at->format('Y-m-d H:i') : 'Not yet',
                        $v->expires_at ? $v->expires_at->format('Y-m-d H:i') : 'N/A',
                        $v->created_at->format('Y-m-d H:i'),
                    ]);
                }
            });

            fclose($handle);
        }, 200, $headers);
    }
}
