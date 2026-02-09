<?php

namespace App\Events;

use App\Models\Order;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OrderShipped implements ShouldDispatchAfterCommit
{
    // An event class is essentially a data container which holds the information related to the event.
    // This event class contains no logic.

    // If we want to dispatch the event after a database transaction of controller, just define ShouldDispatchAfterCommit interface.
    
    // Manual dispatch in controller: event(new OrderShipped($order))
    // If we want to use static dispatch() method:
    use Dispatchable;

    // When you push an Event or Job to a queue (like Redis or a database), Laravel has to convert your PHP object into a string (a process called serialization) so it can be stored.
    // SerializesModels trait is the secret sauce that makes queuing heavy objects (like Database models) efficient and safe.
    use SerializesModels;

    public function __construct(
        public Order $order,
    ) {}
}