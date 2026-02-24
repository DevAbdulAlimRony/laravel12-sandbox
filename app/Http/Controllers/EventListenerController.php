<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class EventListenerController
{
    public function storeOrder(Request $request){
        // Events provide observer pattern implementation- subscribe and listen for various events.
        // An event can be anything from a user registering on your application, a user logging in, or a user placing an order.
        // An event listener is a class that handles the event when it is fired like sending a welcome email after user registration.
        // An event can have multiple listeners. A lsistener can listen to multiple events.

        //* Creating Events:
        // php artisan make:event EventName. n the App\Events directory
        // php artisan make:listener ListenerName.
        // Together: php artisan make:listener SendPodcastNotification --event=PodcastProcessed
        // See App\Events and App\Listeners directories for created files.
        // Can type-hint any dependencies in constructor of Event and Listener, automatically resolved by service container.

        // Store Listener in different directory:
        // See withEvents() in bootstrap\app.php

        // Rather than listening directly in listener, we can manually listen.
        // See Event.listen in AppServiceProvider.php

        // See all Events: php artisan event:list
        // Cache in production to boost performance: php artisan event:cache or using optimize.
        // Clear cache: php artisan event:clear

        //* Calling or Dispatching Events:
        // Any arguments passed to the static dispatch method will be passed to the event's constructor.
        $order = Order::findOrFail($request->order_id);
        // ... Other Controller Logic.
        OrderShipped::dispatch($order);
        OrderShipped::dispatchIf($condition, $order); // Conditional Dispatch.
        OrderShipped::dispatchUnless($condition, $order);

        //* Defer Event:
        // When a User is created, defer sending a "Welcome" ping to an external API
        Event::defer(function () {
            // Some slow API call
            Http::post('https://analytics.com/log', ['event' => 'new_user']);
        }, ['eloquent.created: '.User::class]);

        //* Event Subscriber:
        // An Event Subscriber is a class that allows you to group multiple event listeners into a single location.
        // Think of a standard Listener as a "Specialist" (one person, one job) and a Subscriber as a "Department Manager" (one person handling several related jobs).
        // The "Messy Room" Problem: If you have a complex system like an Order Management System, you might end up with dozens of separate listener files- SendEmail, UpdateInventory, NotifyWareHouse, ApplyLoyaltiPoints.
        // A Subscriber allows you to put all "Order-related" logic into one clean file.
        // See Listeners/OrderEventSubscriber.php.

        //* Queued Event Listeners:
        // Queueing listeners can be beneficial if your listener is going to perform a slow task such as sending an email or making an HTTP request.
        // Configure a queue and run a worker at first.
        // Add ShouldQueueinterface in listener class, See SendShipmentNotification listener class.

        //* Testing:
        // Using the Event facade's fake method, may prevent listeners from executing
        // Use: assertDispatched(), assertNotDispatched(), assertNothingDispatched()
        // test_orders_can_be_shipped().
        // Event::assertListening()
        // Can fake a subset of events.
    }
}