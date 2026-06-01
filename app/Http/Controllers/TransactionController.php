<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Transaction;
use App\Models\Wallet;

class TransactionController extends Controller
{
public function store(Request $request)
{
    \Log::info($request->all());

    $wallet = Wallet::find($request->wallet_id);

    if (!$wallet) {
        return response()->json([
            'message' => 'Wallet tidak ditemukan'
        ], 404);
    }

    $transaction = Transaction::create([
        'wallet_id' => $wallet->id,
        'type' => $request->type,
        'amount' => $request->amount,
        'category' => $request->category ?? null,
        'target' => $request->target ?? null
    ]);

    if ($request->type === 'topup') {

        $wallet->saldo += $request->amount;
    }

    if ($request->type === 'transfer') {

        if ($wallet->saldo < $request->amount) {

            return response()->json([
                'message' => 'Saldo tidak cukup'
            ], 400);
        }

        $wallet->saldo -= $request->amount;
    }

    $wallet->save();

    return response()->json([
        'message' => 'Transaction success',
        'transaction' => $transaction,
        'saldo' => $wallet->saldo
    ]);
}

    public function index($wallet_id)
    {
        $transactions = Transaction::where('wallet_id', $wallet_id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($transactions);
    }
}