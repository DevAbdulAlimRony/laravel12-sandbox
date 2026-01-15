<?php
// Create fake data:
// @for ($i = 0; $i < 10; $i++)
//     <dl>
//         <dt>Name</dt>
//         <dd>{{ fake()->name() }}</dd>

//         <dt>Email</dt>
//         <dd>{{ fake()->unique()->safeEmail() }}</dd>
//     </dl>
// @endfor
// Use: APP_FAKER_LOCALE in env, define in app.php config as faker_locale
// ustom locale use: fake('nl_NL')->name()

// Execute give callback, cache it and use again if calls to the once:
function random(): int
{
    return once(function () {
        return random_int(1, 1000);
    });
}
random(); // 123
random(); // 123 (cached result)
// If from a class, we make another object of that class, will get cached result again.

// Return null if object is null rather than error.
optional($user->address)->street; // If $user object not found, then retrun null rather than error.
return optional(User::find($id), function (User $user) {
    return $user->name;
}); // Execute the closure if User::find($id) is not null.

// Executes the given closure and cathes exception during execution:
return rescue(function () {
    return $this->method();
}); // pass second argument or closure to return if any error occur rather than throwing exception.
return rescue(function () {
    return $this->method();
}, report: function (Throwable $throwable) {
    return $throwable instanceof InvalidArgumentException;
});

// Retry execution for a callback untill the maximum ateemp met.
return retry(5, function () {
    // Attempt 5 times while resting 100ms between attempts...
}, 100);
// If manually calculate 100ms,use function() ()..
// [100, 200]: Sleep for 100ms on first retry, 200ms on second retry.
// You can pass third callback as argument to retry under a specific condition.
return retry(5, function () {
    // ...
}, 100, function (Exception $exception) {
    return $exception instanceof TemporaryException;
});

// Transform: executes a closure on a given value if the value is not blank and then returns the return value of the closure
 transform(5, fn(int $v) => $v * 2);
// Default value can be passed as third argument.

// The tap() function takes a value, passes it into a closure (callback), and then returns the value.
return tap(User::create($userData), function ($user) {
    $user->assignRole('editor');
    $user->sendWelcomeEmail();
});
// Without tap, you would have to save the $user to a variable, call the methods, and then write return $user at the end. tap makes it a single, fluid "thought."
// Tappable Trait: The Tappable trait allows you to chain a tap() method directly onto a class instance rather than using like a helper. $object->tap()

// With Helper:
// The with($value, $callback) helper takes a value and passes it into a closure. It returns the result of the closure.
$finalTotal = with($array['products']['desk']['price'], function ($price) {
    $discount = $price > 150 ? 20 : 0;
    $tax = $price * 0.10;
    return $price - $discount + $tax;
});
// $discount and $tax don't exist outside here. Clean!

// The when($value, $callback, $default) helper is used to execute a closure based on a condition and return the result.
$stockStatus = when(
    $array['products']['chair']['stock'] > 0,
    fn() => 'In Stock: Ready to Ship',      // If True
    fn() => 'Out of Stock: Pre-order now'   // If False
);
echo $stockStatus; // Output: "Out of Stock: Pre-order now"


// Use of Literal (std class instance):
use Illuminate\Support\Facades\Auth;
use Illuminate\Auth\Access\AuthorizationException;

$promoProduct = literal(
    name: 'Limited Edition Desk',
    price: 150,
    description: 'A special discounted mahogany desk.'
);
return view('promotions.show', ['product' => $promoProduct]);
// Now, we can access it: {{ $product->name }}
