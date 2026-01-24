<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StorePostRequest extends FormRequest
{
    protected $stopOnFirstFailure = true; // Stop on First Failure:
    protected $redirect = '/dashboard'; // Redirect Loacation, or by route:
    protected $redirectRoute = 'dashboard';

    /**
     *  If the authenticated user actually has the authority to update a given resource.
     */
    // If have plan to handle authorization logic for the request in another part of your application, you may remove the authorize method completely, or simply return true.
    // Can type-hint any dependeny here.
    public function authorize(): bool
    {
        return true;
        // return $comment && $this->user()->can('update', $comment);
        // If the authorize method returns false, an HTTP response with a 403 status code will automatically be returned and your controller method will not execute.
    }

    // Prepare Data for Validation: Convert data to a new format before doing validation
    protected function prepareForValidation(): void
    {
        $this->merge([
            // Strip all non-numeric characters before validation
            'phone_number' => preg_replace('/[^0-9]/', '', $this->phone_number),
        
            // Ensure email is always lowercase to prevent duplicate check issues
            'email' => strtolower($this->email),
        
            // Combine 'country_code' and 'mobile' for a specific rule
            'full_phone' => $this->country_code . $this->phone_number,
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        // Can type-hint any dependeny, laravel will automatically resolve it via the service container.
        return [
            'title' => 'required|unique:posts|max:255',
        ];
    }

    // Perform additional validation after initial validation:
    public function after(): array{
            return [
                // Can add invokable class also:
                new ValidateUserStatus,
                new ValidateShippingTime,

                function (Validator $validator){
                    $balance = $this->user()->balance;

                    if ($this->amount > $balance) {
                        $validator->errors()->add('amount', 'Insufficient funds in your account.');
                    }
                }
            ];
    }

    // Customizing error messages:
    public function messages(): array{
        return [
            'title.required' => 'A title is required',
        ];
    }

    // Customizing the validation attribute:
    // If you would like the :attribute placeholder of your validation message to be replaced with a custom attribute name
    public function attributes(): array{
        return [
            'email' => 'email address'
        ];
    }

    // Normalize any data after passing the validation:
    protected function passedValidation(): void
    {
        // After we know lat/long are valid numbers, format them for the DB
        $this->replace([
            'location' => "POINT({$this->longitude} {$this->latitude})",
        
            // Or, auto-hash a password so the Controller doesn't have to
            'password' => Hash::make($this->password),
        
            // Set a default value that shouldn't be overridden by the user
            'status' => 'pending_review',
        ]);
    }
}
