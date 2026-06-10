<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Wallet;
use App\Http\Controllers\TransactionController;
use App\Models\NotificationSetting;
use App\Http\Controllers\SavingController;
use App\Http\Controllers\PasswordController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\PasswordResetController;

// ==========================
// TRANSACTIONS
// ==========================
Route::post('/transactions', [TransactionController::class, 'store']);
Route::get('/transactions/{wallet_id}', [TransactionController::class, 'index']);

//SAVINGS
Route::post(
    '/savings',
    [SavingController::class, 'store']
);

Route::get(
    '/savings/{user_id}',
    [SavingController::class, 'total']
);

Route::get(
    '/savings-history/{user_id}',
    [SavingController::class, 'history']
);

Route::delete(
    '/savings/{id}',
    [SavingController::class, 'destroy']
);

// ==========================
// WALLET (AMBIL SALDO PER WALLET)
// ==========================
Route::get('/wallet/{wallet_id}', function($wallet_id){

    $wallet = Wallet::find($wallet_id);

    if(!$wallet){
        return response()->json([
            'saldo' => 0
        ]);
    }

    return response()->json([
        'saldo' => $wallet->saldo
    ]);
});


// ==========================
// CONNECT WALLET
// ==========================
Route::post('/connect-wallet', function(Request $request){

    $user_id = $request->user_id;
    $wallet_id = $request->wallet_id;

    $wallet = Wallet::find($wallet_id);

    if(!$wallet){
        return response()->json([
            'message' => 'Wallet tidak ditemukan'
        ],404);
    }

    // MATIKAN WALLET SEJENIS YANG SUDAH CONNECT
    $sameWalletIds = Wallet::where(
        'wallet',
        $wallet->wallet
    )->pluck('id');

    DB::table('wallet_connections')
        ->where('user_id', $user_id)
        ->whereIn('wallet_id', $sameWalletIds)
        ->update([
            'connected' => 0,
            'updated_at' => now()
        ]);

    $existing = DB::table('wallet_connections')
        ->where('user_id', $user_id)
        ->where('wallet_id', $wallet_id)
        ->first();

    if($existing){

        DB::table('wallet_connections')
            ->where('id', $existing->id)
            ->update([
                'connected' => 1,
                'updated_at' => now()
            ]);

    } else {

        DB::table('wallet_connections')->insert([
            'user_id' => $user_id,
            'wallet_id' => $wallet_id,
            'connected' => 1,
            'created_at' => now(),
            'updated_at' => now()
        ]);

    }

    return response()->json([
        'message' => 'Wallet connected'
    ]);
});

//DISCONNECT WALLET
Route::post('/disconnect-wallet', function(Request $request){

    DB::table('wallet_connections')
        ->where('user_id', $request->user_id)
        ->where('wallet_id', $request->wallet_id)
        ->update([
            'connected' => 0,
            'updated_at' => now()
        ]);

    return response()->json([
        'message' => 'Wallet disconnected'
    ]);
});

// ==========================
// GET WALLET STATUS
// ==========================
Route::get('/wallet-status/{user_id}', function($user_id){

    $data = DB::table('wallet_connections')
        ->join(
            'wallets',
            'wallet_connections.wallet_id',
            '=',
            'wallets.id'
        )
        ->where('wallet_connections.user_id', $user_id)
        ->select(
            'wallet_connections.wallet_id',
            'wallet_connections.connected',
            'wallets.wallet',
            'wallets.account_name',
            'wallets.saldo'
        )
        ->get();

    return response()->json($data);
});


// ==========================
// LOGIN
// ==========================
Route::post('/login', function(Request $request){

    $request->validate([
        'email' => 'required|email',
        'password' => 'required'
    ]);

    $user = User::where('email', $request->email)->first();

    if(!$user || !Hash::check($request->password, $user->password)){
        return response()->json([
            'message' => 'Email atau password salah'
        ], 401);
    }

    return response()->json(
        $user->only([
            'id',
            'name',
            'email',
            'first_name',
            'last_name',
            'nickname',
            'gender',
            'language'
        ])
    );
});


// ==========================
// REGISTER
// ==========================
Route::post('/register', function(Request $request){

    $validator = Validator::make($request->all(), [
        'username' => 'required|string|max:100',
        'email' => 'required|email|unique:users,email',
        'password' => [
            'required',
            'min:8',
            'regex:/[a-z]/',
            'regex:/[A-Z]/',
            'regex:/[0-9]/',
            'regex:/[@$!%*#?&]/'
        ],
    ]);

    if ($validator->fails()) {
        return response()->json([
            'errors' => $validator->errors()
        ], 422);
    }

    try {

        $user = User::create([
            'name' => $request->username,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        return response()->json([
            'message' => 'Register berhasil',
            'user' => $user
        ]);

    } catch (\Exception $e) {

        return response()->json([
            'error' => $e->getMessage()
        ], 500);

    }

});


// ==========================
// UPDATE PROFILE
// ==========================
Route::post('/profile/update', function(Request $request){

    $user = User::find($request->user_id);

    if(!$user){
        return response()->json(['message' => 'User tidak ditemukan'], 404);
    }

    $user->first_name = $request->firstName;
    $user->last_name = $request->lastName;
    $user->nickname = $request->nickname;
    $user->gender = $request->gender;
    $user->language = $request->language;

    $user->save();

    return response()->json([
        'message' => 'Profile berhasil diupdate',
        'user' => $user
    ]);

});


// ==========================
// GET USER PROFILE
// ==========================
Route::get('/user/{id}', function($id){

    $user = User::find($id);

    if(!$user){
        return response()->json(['message' => 'User tidak ditemukan'], 404);
    }

    return response()->json($user);
    
});

// ==========================
// REGISTER WALLET
// ==========================
Route::post('/wallet/register', function(Request $request){

    $exists = Wallet::where('wallet', $request->wallet)
        ->where('account_name', $request->account_name)
        ->first();

    if($exists){
        return response()->json([
            'message' => 'Nomor sudah terdaftar'
        ], 400);
    }

    $wallet = Wallet::create([
        'wallet' => $request->wallet,
        'account_name' => $request->account_name,
        'password' => Hash::make($request->password),
        'saldo' => 0
    ]);

    return response()->json([
        'message' => 'Register berhasil',
        'wallet' => $wallet
    ]);
});


// ==========================
// LOGIN WALLET
// ==========================
Route::post('/wallet/login', function(Request $request){

    $wallet = Wallet::where('wallet', $request->wallet)
        ->where('account_name', $request->account_name)
        ->first();

    if(!$wallet){
        return response()->json([
            'message' => 'Akun tidak ditemukan'
        ], 404);
    }

    if(!Hash::check($request->password, $wallet->password)){
        return response()->json([
            'message' => 'Password salah'
        ], 401);
    }

    return response()->json([
        'message' => 'Login berhasil',
        'wallet' => $wallet
    ]);
});

//NOTIFICATION SETTINGS
Route::get('/notification-settings/{user_id}', function ($user_id) {

    $setting =
        NotificationSetting::firstOrCreate(
            ['user_id' => $user_id]
        );

    return response()->json($setting);
});

//UPDATE NOTIFICATION SETTINGS
Route::put(
'/notification-settings/{user_id}',
function(Request $request, $user_id){

    $setting =
        NotificationSetting::firstOrCreate(
            ['user_id' => $user_id]
        );

    $setting->update([

        'daily_summary' =>
            $request->daily_summary,

        'weekly_report' =>
            $request->weekly_report,

        'spending_alert' =>
            $request->spending_alert,

        'alert_threshold' =>
            $request->alert_threshold,

        'monthly_budget' =>
            $request->monthly_budget,

        'savings_goal_enabled' =>
            $request->savings_goal_enabled,

        'monthly_savings_goal' =>
            $request->monthly_savings_goal

    ]);

    return response()->json([
        'message' => 'saved'
    ]);
});

//CHANGE PASSWORD
Route::post(
    '/change-password',
    [PasswordController::class, 'change']
);

//REPORT
Route::get(
    '/report/{userId}',
    [ReportController::class, 'generate']
);

//PASSWORD RESET
Route::post(
    '/forgot-password',
    [PasswordResetController::class, 'sendOtp']
);

Route::post(
    '/verify-otp',
    [PasswordResetController::class, 'verifyOtp']
);

Route::post(
    '/reset-password',
    [PasswordResetController::class, 'resetPassword']
);