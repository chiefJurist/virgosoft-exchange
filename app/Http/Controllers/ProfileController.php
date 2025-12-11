<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ProfileController extends Controller
{
    //
    public function index(Request $request)
    {
        $user = $request->user()->load('assets');

        return response()->json([
            'balance' => $user->balance,
            'assets'  => $user->assets->keyBy('symbol')->map(fn($a) => [
                'amount'        => $a->amount,
                'locked_amount' => $a->locked_amount,
                'available'     => $a->amount - $a->locked_amount,
            ]),
        ]);
    }
}
