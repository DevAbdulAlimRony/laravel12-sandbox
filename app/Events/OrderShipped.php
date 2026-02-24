<?php

namespace App\Events;

use App\Models\Order;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\PrivateChannel;

class OrderShipped implements ShouldDispatchAfterCommit, ShouldBroadcast
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

    //* Event Broadcasting:
    // implements ShouldBroadcast interface.
    // Get the channel the event should broadcast on:
    public function broadcastOn(): Channel{
        return new PrivateChannel('orders.'.$this->order->id);
        // If multiple channels: return [].
        // The channels should be instances of Channel, PrivateChannel, or PresenceChannel.
        // Instances of Channel represent public channels that any user may subscribe to/
        // rivateChannels and PresenceChannels represent private channels that require channel authorization.
        // Private channel should be autorized which is done in routes/channels.php.
    }

    //* By default, laravel will broadcast the evnt using event's name. We can override it:
    public function broadcastAs(): string
    {
        return 'server.created';
    }

    //* When an event is broadcast, all of its public properties are automatically serialized and broadcast as the event's payload, can access in frontend. order->created_at.
    // But we have control over it:
    public function broadcastWith(): array{
        return ['id' => $this->user->id];
    }

    //* By default, each broadcast event is placed on the default queue for the default queue connection.
    // But we have control over it:
    public $connection = 'redis';
    public $queue = 'default'; // or, 
    public function broadcastQueue(): string{
        return 'default';
    }
    // To use sync queue: implements ShouldBroadcastNow

    //* Broadcast only if a given condition is true:
    public function broadcastWhen(): bool{
        return $this->order->payable > 100;
    }

    //* When working with Database Transaction:  implements ShouldDispatchAfterCommit.

    //* Rescuing Broadcasts:
    // If queue server is unavailable or Laravel encounters an error while broadcasting an event, an exception is thrown that typically causes the end user to see an application error.
    //  can prevent these exceptions from disrupting the user experience:  implements ShouldBroadcast, ShouldRescue.
    // This helper catches any exceptions, reports them to your application's exception handler for logging, and allows the application to continue executing normally without interrupting the user's workflow.

    //* Presence Channels:
    // Presence channels build on the security of private channels while exposing the additional feature of awareness of who is subscribed to the channel. 
    // For example, notifying users when another user is viewing the same page or listing the inhabitants of a chat room.
    // All presence channels are also private channels; therefore, users must be authorized to access them.
    // But while authorizing, insteade of returning true, return array of data.
    // We can join presence channel: Echo.join().here().joining().leaving().error().

    //* Model Broadcasting:
    // Broadcast when model created, updated, deleted.
    // See Flight Model.
}