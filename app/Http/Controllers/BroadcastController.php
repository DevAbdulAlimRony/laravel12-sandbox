<?php

namespace App\Http\Controllers;
use App\Events\OrderShipped;
use Illuminate\Support\Facades\Broadcast;

class BroadcastController{
    // A standard web request (HTTP) is like a Postcard: You send a message, wait a few days, and get a response back.
    // A WebSocket is like a Phone Call: Once the connection is established, the line stays open. Both people can talk at the same time, instantly, until someone hangs up.
    // Laravel's event broadcasting allows you to broadcast your server-side Laravel events to your client-side JavaScript application using a driver-based approach to WebSockets.
    // When some data is updated on the server, a message is typically sent over a WebSocket connection to be handled by the client.
    // Broadcasting your Laravel events allows you to share the same event names and data between your server-side Laravel application and your client-side JavaScript application.
    // Clients connect to named channels on the frontend, while your Laravel application broadcasts events to these channels on the backend.
    // Available server side channel: Laravel Reverb, Pusher Channels, Ably and a log driver for local development and debugging. null driver to disable during testing.
    // For Laravel's React or Vue strter kits, we can listen for events using Echo's useEcho hook.
    // Laravel Echo is a JavaScript library that makes it painless to subscribe to channels and listen for events broadcast by your server-side broadcasting driver.
    //  All event broadcasting is done via queued jobs. Before broadcasting any event, configure and run a queue worker.

    // Enable Broadcasting: php artisan install:broadcasting
    // config/broadcasting.php, routes/channels.php
    // php artisan install:broadcasting --reverb. If already broadcasting installed, php artisan install:broadcasting --reverb. or just install broadcast, it will ask for the driver to install.
    // After installing reverb, publish configs and env vars: php artisan reverb:install
    // Same goes for Pusher, just have to give credentials in env like PUSHER_APP_ID etc. BROADCAST_CONNECTION=pusher.
    // Same goes for Ably.
    // When installing Reverb or any driver, client side Echo will be installed automatically. Manual:
    // npm install --save-dev laravel-echo pusher-js
    // In resources/js/bootstrap.js: configureEcho({});

    // Now, see example in Events/OrderShipped.php.
    // After broadcasting in event, we have to listen it in frontent:
    // import { useEcho } from "@laravel/echo-vue";
    // useEcho(`orders.${orderId}`, "OrderShipment", (e) => {console.log(e.order);})
    // If we give broadcast name using broadcastAs, then should use dot at first: listen('.server.created', function (e){}).
    
    // When broadcasting is installed Laravel attempts to automatically register the /broadcasting/auth route to handle authorization requests.
    // If failed, then manually in app.php withRouting add channels route.
    // php artisan channel:list

    //* Broadcasting Events:
    OrderShipped::dispatch($order); // Just dispatch the event which implements shouldBroadcast.
    broadcast(new OrderShipped($update))->toOthers(); // Broadcast to all subscribers except current user.
    // When we initialize a Laravel Echo instance, a socket ID is assigned to the connection. 
    // The socket ID will automatically be attached to every outgoing request as an X-Socket-ID header if we use axios instance.
    // If we dont use axios, we have to manually extract that socket id for each request: var socketId = Echo.socketId();
    broadcast(new OrderShipped($update))->via('pusher'); // customized broadcast connection while using multiple connections. or, 
    // $this->broadcastVia('pusher'); in Event's constructor. 

    //* Anonymous Event: without creating a dedicated event class.
    Broadcast::on('orders.'.$order->id)->send(); // {"event": "AnonymousEvent", "data": "[]", "channel": "orders.1"}
    Broadcast::on('orders.'.$order->id)->as('OrderPlaced')->with($order)->send();
    Broadcast::private('orders.'.$order->id)->send();
    Broadcast::presence('channels.'.$channel->id)->send();
    Broadcast::on('orders.'.$order->id)->sendNow(); // sync queue.
    Broadcast::on('orders.'.$order->id)->toOthers()->send();

    //* Listening Broadcast using Echo:
    // https://laravel.com/docs/12.x/broadcasting#receiving-broadcasts


    //* Client Events:
    // Broadcast event without hitting backend, Examp: Another user is typing.
    // Use Echo's whisper method.
    // const { channel } = useEcho(`chat.${roomId}`, ['update'], (e) => {})
    // channel().whisper('typing', { name: user.name }); channel().listenForWhisper('typing', (e) => {})

    //* Notifications:
    // By pairing event broadcasting with notifications, JavaScript application may receive new notifications as they occur without needing to refresh the page.
    // const { channel } = useEchoModel('App.Models.User', userId);
    // channel().notification((notification) => {})
    // stop listening to notifications without leaving the channel: stopListeningForNotification(callback)
}
