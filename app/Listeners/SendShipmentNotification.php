<?php

namespace App\Listeners;

use App\Events\OrderShipped;

class SendShipmentNotification
{
    public function __construct() {}

    // Here, we can perform any operation.
    public function handle(OrderShipped $event): void
    {
        // Access the order using $event->order...
        // If we want to stop propagation of OrderShipped event to other listeners, we can return false here.
    }
}