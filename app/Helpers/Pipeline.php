<?php

// Pipeline facade provides a convenient way to "pipe" a given input through a series of invokable classes, closures, or callables, giving each class the opportunity to inspect or modify the input and invoke the next callable in the pipeline.
// Each invokable class or closure in the pipeline is provided the input and a $next closure. 
// Invoking the $next closure will invoke the next callable in the pipeline.
// This is very similar to middleware.

// When the last callable in the pipeline invokes the $next closure, the callable provided to the then method will be invoked. 
// If you simply want to return the input after it has been processed, you may use the thenReturn method.
// In pipeline, You may also provide invokable classes beside closure.
//  If a class name is provided, the class will be instantiated via Laravel's service container.

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Pipeline;


// $user = Pipeline::send($user)
//     ->through([
//         GenerateProfilePhoto::class,
//         ActivateSubscription::class,
//         SendWelcomeEmail::class,
//     ])
//     ->thenReturn();
// or, then(fn (User $user) => $user): Do something

// If we want all steps of the pipeline within a single database transaction:
// Use: ->withinTransaction()

// Real life Example:
    // Validate the order data
    // Apply any active discount codes\
    // Calculate shipping costs
    // Add applicable taxes
    // Check inventory levels
    // Apply any customer loyalty points
    // Send notifications to relevant parties
// Without Pipeline: We have to write so many if condition and all methods to call.
// But with Pipeline:
// public function processOrder(Request $request)
// {
//     $order = Order::find($request->order_id);

//     return Pipeline::send($order)
//                     ->through([
//                         ValidateOrder::class,
//                         ApplyDiscountCodes::class,
//                         CalculateShipping::class,
//                         CalculateTaxes::class,
//                         CheckInventory::class,
//                         ApplyLoyaltyPoints::class,
//                         SendNotifications::class,
//         ])
//         ->thenReturn();
// }

// And build all pipeline class-
final class ApplyDiscountClass{
    // Inject dependencies if need.
    public function handle(
        // Return to the next closure or class.
    )
    {
    }
}

// Another Example: Complex Multi-Stage Data Filtering, Sorts, Pagination in a Pipeline.
