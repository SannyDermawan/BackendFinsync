<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\PasswordOtp;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class PasswordResetController extends Controller
{
    public function sendOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email'
        ]);

        $user = User::where(
            'email',
            $request->email
        )->first();

        if (!$user) {
            return response()->json([
                'message' => 'Email tidak ditemukan'
            ], 404);
        }

        $otp = rand(100000, 999999);

        PasswordOtp::updateOrCreate(
            [
                'email' => $request->email
            ],
            [
                'otp' => $otp,
                'expired_at' => now()->addMinutes(10)
            ]
        );

        Mail::raw(
            "Kode OTP FinSync Anda: $otp",
            function ($message) use ($request) {
                $message
                    ->to($request->email)
                    ->subject('FinSync OTP');
            }
        );

        return response()->json([
            'message' => 'OTP berhasil dikirim'
        ]);
    }

    public function verifyOtp(Request $request)
    {
        $otpData = PasswordOtp::where(
            'email',
            $request->email
        )
        ->where(
            'otp',
            $request->otp
        )
        ->first();

        if (!$otpData) {
            return response()->json([
                'message' => 'OTP salah'
            ], 400);
        }

        if (
            Carbon::now()->gt(
                $otpData->expired_at
            )
        ) {
            return response()->json([
                'message' => 'OTP expired'
            ], 400);
        }

        return response()->json([
            'message' => 'OTP valid'
        ]);
    }

    public function resetPassword(Request $request)
    {
        $user = User::where(
            'email',
            $request->email
        )->first();

        if (!$user) {
            return response()->json([
                'message' => 'User tidak ditemukan'
            ], 404);
        }

        $user->update([
            'password' => Hash::make(
                $request->password
            )
        ]);

        PasswordOtp::where(
            'email',
            $request->email
        )->delete();

        return response()->json([
            'message' => 'Password berhasil diubah'
        ]);
    }
}