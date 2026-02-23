<?php

namespace App\Broadcasting;

use App\Models\Order;
use App\Models\User;

class OrderChannel
{
    // Create a new channel instance.
    // Channel classes will automatically be resolved by the service container.
    // Can type-hint any necessary dependencies.
    public function __construct() {}

    /**
     * Authenticate the user's access to the channel. Method name must be join().
     */
    public function join(User $user, Order $order): array|bool
    {
        return $user->id === $order->user_id;
    }
}