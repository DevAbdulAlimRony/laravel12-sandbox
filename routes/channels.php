<?php
use App\Models\Order;
use App\Models\User;
use App\Broadcasting\OrderChannel;

Broadcast::channel('orders.{orderId}', function (User $user, int $orderId) {
    return $user->id === Order::findOrNew($orderId)->user_id;
});

// Instead of using callback, we can use channel classes to authorize channels:
// php artisan make:channel OrderChannel
Broadcast::channel('orders.{order}', OrderChannel::class);
// See the class in app/broadcasting.

// We can use implicit or explicit rpute model binding: Broadcast::channel('orders.{order}'.
// Rather than orderId, we passed Order model itself.
// Unlike HTTP route model binding, channel model binding does not support automatic implicit model binding scoping.

// May assign multiple, custom guards that should authenticate the incoming request if necessary:
Broadcast::channel('channel', function () {}, ['guards' => ['web', 'admin']]);

// Presence Channel Authorization:
Broadcast::channel('chat.{roomId}', function (User $user, int $roomId) {
    if ($user->canJoinRoom($roomId)) {  return ['id' => $user->id, 'name' => $user->name];
}});