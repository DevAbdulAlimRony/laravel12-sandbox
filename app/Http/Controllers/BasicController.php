<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Routing\Controllers\HasMiddleware;

//* class TransactionController extends Controller
class BasicController implements HasMiddleware
{
   //* Extending from Controller is not required.
   // If the Controller class is always empty, you dont have any shared thing then recommended to remove the extends Controller.
   // That parent Controller was helpful in laravel 10, it provided some useful methods. But after Laravel 10, it is not required.
   
   //* Dependency Injection: If we have custom class(service/repository/anyTypeCustomClass), we can inject it directly in method like show(calculationService $service) or in constructor to use in all methods.
   //* Method Injection: if laravel's class like Request then injecting directly in method is standard and recommended.
   //* Sevice Container: Tool for managing class dependencies and performing dependency injection. 
   // If we inject a concrete class as a dependency in a class, laravel will automatically instantiate the class recursively into service container.
   // Recursively means, if that dependency has another dependency, it will be binded also.
   // But if we inject any interface or abstract class, we will get error. Laravel doesn't bind them automatically.
   public function __construct(
        protected TransactionTestRepository $users,

        // Let's say we have a PaymentProcessor Interface in app/concontracts which have to bind in the service container.
        private readonly PaymentProcessor $paymentProceesor,
        // Now, bind it in AppServiceProvider's register method.
   ) {}

   public function index(): string{
        //* Resolving Dependency outside of the constructor
        // Rather than in constructor, we can inject dependency outside using App facade or app or resolve helper function.
        app()->make(PaymentProcessor::class);
        App::make(PaymentProcessor::class);
        resolve(PaymentProcessor::class);
        // See here, we injected Bkash as PaymentProcessor dependency in constructor, and in here three times.
        // So, we will get result four times. There singleton come, see in AppServiceProvider, we used singleton() rather than bind().
        // Laravel's service container implements the PSR-11 interface. So,
        // Another way is injecting Container itself in constructor:
        //  __construct(private readonly ContainerInterface $container){}.. and access: $this->container->make(PaymentProcessor::class)
        // If you want to digging deeper how container works, Go to the Application.php which extends from Container, and observe the Container.php resolve() method.
        // App::call([new PodcastStats, 'generate']);
        // Check if a class binded explicitely: if($this->app->bound(Transistor::class))

        // Generating URLs to Named Routes:
        route('transactions'); // We passed the route name, it will generate full url
        return 'Transaction';
   }

   public function show(int $transactionId): string{
        route('transactions', parameters: ['transactionId' => 5]);
       // return to_route('transactions', parameters: ['transactionId' => 5]); // Redirect
        
        // Return the blade file with user data
         return view('user.profile', [
            'transaction' => TransactionTest::findOrFail($transactionId)
        ]);
   }

   public function create(): string{
        return 'form to create a transaction';
   }

   public function store(Request $request): string{
        return "Transaction created";
   }

   //* Middleware in Controller!!
   // Beside defining in route, we can define middleware in controller also
   // Step1: implements HasMiddleware interface
   // Step2: implement middleware method
    public static function middleware(): array
    {
        return [
            'auth',
            new Middleware('log', only: ['index']),
            new Middleware('subscribed', except: ['store']),
        ];
    } // or we can make a closure and implement it there.

    //* Model: See Flight Model in directory.
}
