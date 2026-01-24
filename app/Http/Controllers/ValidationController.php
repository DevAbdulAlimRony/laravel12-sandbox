<?php

namespace App\Http\Controllers;

class ValidationController {

    //* Using Request instance
    public function index(Request $request){
        // If validation fails, an Illuminate\Validation\ValidationException exception will be thrown and the proper error response will automatically be sent back to the user.
        // For XHR request, laravel will send Json response, {"messae" => "..(and 4 more errors), "errors": {"title",...}"}        
        $request->validate([
            'title' => 'required|unique:posts|min:3|max:255',

            // Rather than single delimited string, rules can be specified in array
            'title2' => ['required', 'unique:posts', 'max:255'],

            // Stop at first validation failure by bail:
            'title3' => 'bail|required||unique:posts|max:255',
            // Rules will be validated in the order they are assigned.
            // If the unique rule on fails, the max rule will not be checked.
            
            // Nested Data alidation:
            'author.name' => 'required',
            'v1\.0' => 'required', // if contains a literal period. "v1.0": "Version Release Notes",
            // The backslash tells Laravel: "This dot is part of the key name, not a nested array"

            // For optional field, we should specify nullable so that validator not consider null values as invalid using ConvertEmptyStringsToNull middleware.
            'publish_at' => 'nullable|date',

            // A credit card number is required if the payment_type has a value of cc
            'credit_card_number' => 'required_if:payment_type,cc'
            // This cc is a value, we can customize it lang files in values array so that in error message it shows credit card rather than cc.

            // Conditionally adding rules:
            // appointment_date field will not be validated if the has_appointment field has a value of false
            'has_appointment' => 'required|boolean',
            'appointment_date' => 'exclude_if:has_appointment,false|required|date', // can use exclude_unless also.

            // Validating when present:
            // email field will only be validated if it is present in the $data array.
            'email' => 'sometimes|required|email',
        ]);

        // Validate and store erropr messages within a named error bag:
        $request->validateWithBag('postErrors', ['title' => 'required']);
    }

    //* Using Form Request
    // For more complex validation, we can use Form Request.
    // php artisan make:request StorePostRequest
    // The generated form request class will be placed in the app/Http/Requests directory.
    // Now just type-hint the Request class here
    public function store(StorePostRequest $request): RedirectResponse{
        // All incoming requests here are valid.

        $validated = $request->validated(); // Retrieve the validated data
        $request->safe();
        $request->safe()->all();
        $validated = $request->safe()->only(['name', 'email']); // Retrive just a portion
        // Same goes for except()
        // foreach ($request->safe() as $key => $value) {
        // $email = $validated['email'];
        // Add additional field: $request->safe()->merge(['name' => 'Taylor Otwell']);
        // Retrieve as a collection:  $request->safe()->collect();

         return redirect('/posts');

         //* Working with error messages:
         // An $errors variable is shared with all of your application's views by the Illuminate\View\Middleware\ShareErrorsFromSession middleware, which is provided by the web middleware group.
         // All default error messages are located in lang/en/validation.php.
         // $errors = $validator->errors(); $errors->first('email');
         // foreach ($errors->get('email') as $message) {}
         // foreach ($errors->all() as $message) {}
         // $errors->has('email'){}

         // Eeoor messages are stored in lang/validations/en
         // Can use custom message, attributes there also.
         // php artisan lang:publish, now we are free to change any message. also can copy for anothe language directory.
         // In blade we can use: @error @enderror to check if error present. {{ $message }

    }

    //* Using Validator Facade:
    public function update(){
        // We can make validator manually to return a validator instance so that we can chain other methods
        // of Validator facade:
        $validator = Validator::make($request->all(), [
            'title2' => ['required', 'unique:posts', 'max:255'],
        ]);

        // if ($validator->fails())
        // $validator->validated();
        // $validator->safe()->except(['name', 'email']); or only.
        // $validator->stopOnFirstFailure()->fails()
        // $validator->validateWithBag('posts')
        $validator->validate(); // Automatic redirection.

        // Named Error Bags: If we have multiple form on same page: 
        redirect('/register')->withErrors($validator, 'login'); // Access it: $errors->login->first('email')

        // Customizing Error Messages:
        $validator = Validator::make($input, $rules, $messages = [
            'required' => 'The :attribute field is required.',
            // :attribute placeholder will be replaced by the actual name of the field under validation.
            'email.required' => 'We need to know your email address!',
        ]);

        // Additional Validation: $validator->after(function ($validator){})
    }

    //* Live Validation using Laravel Precognition Package
    // For Inertia powered frontend, we can do real time validation using Laravel Precognition Package:
    public function show(){

    }
}