<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

// class TransactionController extends Controller
class TransactionController
{
   // Extending from Controller is not required.
   // If the Controller class is always empty, you dont have any shared thing then recommended to remove the extends Controller.
   // That parent Controller was helpful in laravel 10, it provided some useful methods. But after Laravel 10, it is not required.
   // If we have custom service class, we can inject it directly in method like show(calculationService $service) or in constructor to use in all methods.
   // If custom class then injecting constructor, if laravel's class like Request then injecting directly in method is standard and recommended.

   public function index(): string{
        route('transactions'); // We passed the route name, it will generate full url
        return 'Transaction';
   }

   public function show(int $transactionId): string{
        route('transactions', parameters: ['transactionId' => 5]);
       // return to_route('transactions', parameters: ['transactionId' => 5]); // Redirect
        return 'Transaction: ' . $transactionId;
   }

   public function create(): string{
        return 'form to create a transaction';
   }

   public function store(Request $request): string{
        return "Transaction created";
   }
}
