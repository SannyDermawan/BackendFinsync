<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class PasswordController extends Controller
{
    public function change(Request $request)
    {
        $user = User::find($request->user_id);

        if (!$user) {
            return response()->json([
                'message' => 'User tidak ditemukan'
            ], 404);
        }

        if (!Hash::check(
            $request->current_password,
            $user->password
        )) {
            return response()->json([
                'message' => 'Password lama salah'
            ], 400);
        }

        $user->password = Hash::make(
            $request->new_password
        );

        $user->save();

        return response()->json([
            'message' => 'Password berhasil diubah'
        ]);
    }
}