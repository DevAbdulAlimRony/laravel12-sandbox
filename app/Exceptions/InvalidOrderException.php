<?php

namespace App\Exceptions;

use Exception;

class InvalidOrderException extends Exception
{
    public function context(): array
    {
        return ['order_id' => $this->orderId];
    }

    public function report(): void {
        // If we check any condition here and make custom reporting then
        // At last retun false so that if any other exceptio happen, laravel can report from the parent Exception class.
        if(1>2){
            return true;
        }
        return false;

        // We can type-hint any required dependencies of the report method.
    }

    public function render(Request $request): Response{
         // If custom exception class extends any renderable exception class, and we want to render parent's method:
        // Just return false after everything in render method.
    }

    // If we want to logg an exception without showing error message or error page, use report helper functin:
    // catch (Throwable $e) { 
    //      report($e);
    //      return false;
    // }
}