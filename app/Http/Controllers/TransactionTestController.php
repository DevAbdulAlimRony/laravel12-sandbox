<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Routing\Controllers\HasMiddleware;

// class TransactionController extends Controller
class TransactionTestController implements HasMiddleware
{
   // Extending from Controller is not required.
   // If the Controller class is always empty, you dont have any shared thing then recommended to remove the extends Controller.
   // That parent Controller was helpful in laravel 10, it provided some useful methods. But after Laravel 10, it is not required.
   
   // Dependency Injection: If we have custom class(service/repository/anyTypeCustomClass), we can inject it directly in method like show(calculationService $service) or in constructor to use in all methods.
   // Method Injection: if laravel's class like Request then injecting directly in method is standard and recommended.
   public function __construct(
        protected TransactionTestRepository $users,
   ) {}

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

   public function index(): string{
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
}
