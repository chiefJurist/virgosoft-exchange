<?php

namespace App\Http\Controllers;

use App\Events\OrderMatched;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Asset;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    //
    public function openOrders(Request $request)
    {
        $symbol = $request->query('symbol', 'BTC');

        $buyOrders = Order::where('symbol', $symbol)
            ->where('side', 'buy')
            ->where('status', Order::STATUS_OPEN)
            ->orderByDesc('price')
            ->get();

        $sellOrders = Order::where('symbol', $symbol)
            ->where('side', 'sell')
            ->where('status', Order::STATUS_OPEN)
            ->orderBy('price')
            ->get();

        return response()->json([
            'buy'  => $buyOrders,
            'sell' => $sellOrders,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'symbol' => 'required|in:BTC,ETH',
            'side'   => 'required|in:buy,sell',
            'price'  => 'required|numeric|min:0.00000001',
            'amount' => 'required|numeric|min:0.00000001',
        ]);

        return DB::transaction(function () use ($request) {
            $user = $request->user();
            $symbol = $request->symbol;
            $side = $request->side;
            $price = $request->price;
            $amount = $request->amount;
            $total = $price * $amount;

            if ($side === 'buy') {
                if ($user->balance < $total) {
                    return response()->json(['error' => 'Insufficient USD balance'], 400);
                }
                $user->decrement('balance', $total);
            } else {
                $asset = $user->assets()->firstOrCreate(['symbol' => $symbol], ['amount' => 0]);
                if ($asset->amount - $asset->locked_amount < $amount) {
                    return response()->json(['error' => 'Insufficient asset balance'], 400);
                }
                $asset->increment('locked_amount', (float)$amount);
            }

            $order = Order::create([
                'user_id' => $user->id,
                'symbol'  => $symbol,
                'side'    => $side,
                'price'   => $price,
                'amount'  => $amount,
                'status'  => Order::STATUS_OPEN,
            ]);

            $this->tryMatch($order);

            return response()->json($order->fresh(), 201);
        });
    }

    public function cancel(Order $order)
    {
        if ($order->user_id !== Auth::id() || $order->status !== Order::STATUS_OPEN) {
            return response()->json(['error' => 'Cannot cancel this order'], 400);
        }

        return DB::transaction(function () use ($order) {
            $remaining = $order->amount - $order->filled_amount;

            if ($order->side === 'buy') {
                // Release locked USD
                $order->user->increment('balance', $order->price * $remaining);
            } else {
                // Release locked asset
                $order->user->assets()
                    ->where('symbol', $order->symbol)
                    ->decrement('locked_amount', (float) $remaining);
            }

            $order->update(['status' => Order::STATUS_CANCELLED]);

            return response()->json(['message' => 'Order cancelled successfully']);
        });
    }

    private function tryMatch(Order $newOrder)
    {
        if ($newOrder->status !== Order::STATUS_OPEN) return;

        $counterSide = $newOrder->side === 'buy' ? 'sell' : 'buy';
        $priceCondition = $newOrder->side === 'buy' ? '<=' : '>=';

        $counterOrder = Order::where('symbol', $newOrder->symbol)
            ->where('side', $counterSide)
            ->where('status', Order::STATUS_OPEN)
            ->where('price', $priceCondition, $newOrder->price)
            ->orderBy($newOrder->side === 'buy' ? 'price' : 'price desc')
            ->first();

        if (!$counterOrder) return;

        $tradeAmount = min($newOrder->amount, $counterOrder->amount);
        $tradeValue = $tradeAmount * $counterOrder->price; // use maker price
        $commission = $tradeValue * 0.015; // 1.5%

        DB::transaction(function () use ($newOrder, $counterOrder, $tradeAmount, $tradeValue, $commission) {
            // Buyer pays full + commission
            $newOrder->user->decrement('balance', $commission);
            $newOrder->user->assets()->updateOrCreate(
                ['symbol' => $newOrder->symbol],
                ['amount' => 0]
            )->increment('amount', $tradeAmount);

            // Seller receives value minus commission (we take from buyer only)
            $counterOrder->user->increment('balance', $tradeValue);
            $counterOrder->user->assets()->where('symbol', $newOrder->symbol)->decrement('locked_amount', (float)$tradeAmount);

            // Update orders
            $newOrder->increment('filled_amount', $tradeAmount);
            $counterOrder->increment('filled_amount', $tradeAmount);

            if ($newOrder->amount <= $newOrder->filled_amount) {
                $newOrder->update(['status' => Order::STATUS_FILLED]);
            }
            if ($counterOrder->amount <= $counterOrder->filled_amount) {
                $counterOrder->update(['status' => Order::STATUS_FILLED]);
            }

            // Broadcast
            broadcast(new OrderMatched(
                $newOrder->side === 'buy' ? $newOrder : $counterOrder,
                $newOrder->side === 'sell' ? $newOrder : $counterOrder,
                (string) $counterOrder->price,
                (string) $tradeAmount
            ))->toOthers();
        });

        // Recursive match if new order still open
        if ($newOrder->status === Order::STATUS_OPEN) {
            $this->tryMatch($newOrder);
        }
    }
}
