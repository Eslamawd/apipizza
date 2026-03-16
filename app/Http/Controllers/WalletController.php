<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class WalletController extends Controller
{
    public function balance(Request $request)
    {
        return response()->json([
            'balance' => $request->user()->balanceInt / 100,
        ]);
    }
}