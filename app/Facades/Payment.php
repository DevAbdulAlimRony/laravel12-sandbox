<?php
declare(strict_types = 1);

namespace App\Facades;

//* Facades:
// Laravel facades serve as "static proxies" to underlying classes in the service container, providing the benefit of a terse, expressive syntax while maintaining more testability and flexibility than traditional static methods.
// Laravel Facade and Facade design pattern are not same at all, just same name.
// Its actually can be called as proxy design pattern, because it proxies a static method to a class.
// If we dig deeper for Route facade, we will see it has just  getFacadeAccessor static method which returns 'router' string.
// If we inspect Facade parent class, we will see below it call php's __callstatic() magic method.
// So, behind the scene, getFacadeAccessor resolved the provided 'router' string from service container.
// So, actually all facade methods are coming from Router.php class. We think those methods are staic, but actually not.

// In this way, we can create our own facade.
// 1. It must extends Facades class.
// 2. It must call getFacadeAccessor which return the bounded class or class name as a string.
class Payment extends Facades{
    protected static function getFacadeAccessor(): string
    {
        return PaymentProcessor::class;
        // If we bind the PaymentProcessor class in any service provider and it points to the Bkash::class
        // Then if we call Payment::pay(), the pay() method actually from Bkash class.

        // return 'paymentProcessor'; 
        // If we return string, we should bind in service provider as string also.
        // Or, in binding, we can give the class an alias of the same string name.
    }

    // Now, here are things: Let's say Route facade-
    // We can use helper function route()
    // We can use Route::get() facade
    // We can inject Router class iteself and use $router->get()
    // We can inject interface of the Router class
    // All will do the same thing.
    // Don't overuse facade or helper function in a class.
    // Facade is testable as well as dependency injection, no problem.
    // But if we click a facade method from a IDE, it will open just the facadeAccessor which is problematic sometimes.
    // So, choose in your way when to use what.

    // Rather than writting custom facade, using dependency injection is better.
}