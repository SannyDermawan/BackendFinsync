<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Saving;

class SavingController extends Controller
{
    public function store(Request $request)
    {
        $saving = Saving::create([
            'user_id' => $request->user_id,
            'amount' => $request->amount
        ]);

        return response()->json([
            'message' => 'Saving created',
            'saving' => $saving
        ]);
    }

    public function total($user_id)
    {
        $total = Saving::where(
            'user_id',
            $user_id
        )->sum('amount');

        return response()->json([
            'total' => $total
        ]);
    }

    public function history($user_id)
    {
        return Saving::where(
            'user_id',
            $user_id
        )
        ->latest()
        ->get();
    }

    public function destroy($id)
{
    $saving = Saving::find($id);

    if (!$saving) {
        return response()->json([
            'message' => 'Data tidak ditemukan'
        ], 404);
    }

    $saving->delete();

    return response()->json([
        'message' => 'Riwayat berhasil dihapus'
    ]);
}
}