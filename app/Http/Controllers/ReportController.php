<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Transaction;
use App\Models\Saving;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

class ReportController extends Controller
{
    public function generate($userId)
    {
        $startDate = Carbon::now()->subDays(30);

        $walletIds = \DB::table('wallet_connections')
            ->where('user_id', $userId)
            ->where('connected', 1)
            ->pluck('wallet_id');

        $transactions = Transaction::whereIn(
            'wallet_id',
            $walletIds
        )
        ->where(
            'created_at',
            '>=',
            $startDate
        )
        ->get();

        $transactionCount =
            $transactions->count();

        $totalTopup =
            $transactions
                ->where('type', 'topup')
                ->sum('amount');

        $totalTransfer =
            $transactions
                ->where('type', 'transfer')
                ->sum('amount');

        $totalSavings =
            Saving::where(
                'user_id',
                $userId
            )->sum('amount');

        $user = User::findOrFail($userId);

        $currentBalance = \DB::table('wallets')
            ->whereIn('id', $walletIds)
            ->sum('saldo');

        $savingsGoal = \DB::table('notification_settings')
            ->where('user_id', $userId)
            ->value('monthly_savings_goal');

        $progress = 0;

        if ($savingsGoal > 0) {

            $progress = min(
                100,
                round(
                    ($totalSavings / $savingsGoal) * 100
                )
            );

        }
        
        $pdf = Pdf::loadView(
        'report',
        [
            'user' => $user,
            'balance' => $currentBalance,
            'transactions' => $transactionCount,
            'topup' => $totalTopup,
            'transfer' => $totalTransfer,
            'savings' => $totalSavings,
            'goal' => $savingsGoal,
            'progress' => $progress
        ]
    );

        return $pdf->download(
            'FinSync-Report.pdf'
        );
    }
}