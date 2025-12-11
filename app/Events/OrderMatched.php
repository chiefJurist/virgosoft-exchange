<?php

namespace App\Events;

use App\Models\Order;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OrderMatched
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(public Order $buyerOrder, public Order $sellerOrder, public string $price, public string $amount)
    {
        //
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('user.' . $this->buyerOrder->user_id),
            new PrivateChannel('user.' . $this->sellerOrder->user_id),
        ];
    }

    public function broadcastWith(): array
    {
        return [
            'buyer_order_id'  => $this->buyerOrder->id,
            'seller_order_id' => $this->sellerOrder->id,
            'price'           => $this->price,
            'amount'          => $this->amount,
            'timestamp'       => now()->toDateTimeString(),
        ];
    }
}
