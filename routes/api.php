<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Wallet;
use App\Http\Controllers\TransactionController;

// ==========================
// TRANSACTIONS
// ==========================
Route::post('/transactions', [TransactionController::class, 'store']);
Route::get('/transactions/{wallet_id}', [TransactionController::class, 'index']);


// ==========================
// WALLET (AMBIL SALDO PER WALLET)
// ==========================
Route::get('/wallet/{user_id}/{wallet}', function($user_id, $wallet){

    $data = Wallet::where('user_id', $user_id)
        ->where('wallet', $wallet)
        ->first();

    return response()->json([
        'saldo' => $data ? $data->saldo : 0
    ]);

});


// ==========================
// CONNECT WALLET
// ==========================
Route::post('/connect-wallet', function(Request $request){

    $user_id = $request->user_id;
    $wallet_id = $request->wallet_id;

    $existing = DB::table('wallet_connections')
        ->where('user_id', $user_id)
        ->where('wallet_id', $wallet_id)
        ->first();

    if($existing){

        DB::table('wallet_connections')
            ->where('id', $existing->id)
            ->update([
                'connected' => true,
                'updated_at' => now()
            ]);

    } else {

        DB::table('wallet_connections')->insert([
            'user_id' => $user_id,
            'wallet_id' => $wallet_id,
            'connected' => true,
            'created_at' => now(),
            'updated_at' => now()
        ]);

    }

    return response()->json([
        'message' => 'Wallet connected'
    ]);
});


// ==========================
// GET WALLET STATUS
// ==========================
Route::get('/wallet-status/{user_id}', function($user_id){

    $data = DB::table('wallet_connections')
        ->where('user_id', $user_id)
        ->get();

    return response()->json($data);
});


// ==========================
// SALDO OVO
// ==========================
Route::get('/ovo/saldo/{user_id}', function($user_id){

    $wallet = Wallet::where('user_id', $user_id)
        ->where('wallet', 'ovo')
        ->first();

    return response()->json([
        'saldo' => $wallet ? $wallet->saldo : 0
    ]);
});


// ==========================
// SALDO DANA
// ==========================
Route::get('/dana/saldo/{user_id}', function($user_id){

    $wallet = Wallet::where('user_id', $user_id)
        ->where('wallet', 'dana')
        ->first();

    return response()->json([
        'saldo' => $wallet ? $wallet->saldo : 0
    ]);
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