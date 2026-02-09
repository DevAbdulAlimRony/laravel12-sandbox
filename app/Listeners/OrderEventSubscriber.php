<?php
namespace App\Listeners;

use App\Events\OrderPlaced;
use App\Events\OrderCancelled;
use Illuminate\Events\Dispatcher;

class OrderEventSubscriber
{
    public function sendOrderEmail($event) {
        // Logic: Mail::to($event->order->user)->send(new OrderConfirmed($event->order));
    }

    public function updateInventory($event) {
        // Logic: $event->order->items->each->decrementInventory();
    }

    public function notifyWarehouse($event) {
        // Logic: Http::post('https://warehouse.api/orders', $event->order);
    }

    public function applyLoyaltyPoints($event) {
        // Logic: $event->order->user->addPoints(100);
    }

    public function handleCancellation($event) {
        // Logic: Restock items and notify user of refund
    }

    /**
     * Register the listeners for the subscriber.
     */
    public function subscribe(Dispatcher $events): array
    {
        return [
            OrderPlaced::class => [
                [OrderEventSubscriber::class, 'sendOrderEmail'],
                [OrderEventSubscriber::class, 'updateInventory'],
                [OrderEventSubscriber::class, 'notifyWarehouse'],
                [OrderEventSubscriber::class, 'applyLoyaltyPoints'],
            ],
            OrderCancelled::class => [
                [OrderEventSubscriber::class, 'handleCancellation'],
            ],
        ];

        // If listeners in subsriber itself like sendOrderEmail is here, can use shortcut:
        // OrderPlaced::class => 'sendOrderEmail',
    }

    // If we dont follow naming convention of subscribe method, we can manually register subscriber in ServiceProvider
    // See AppServiceProvider.php
}