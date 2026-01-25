<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class Uppercase implements ValidationRule,  DataAwareRule
{
    protected $data = [];
    protected $validator;

    // Custom Validation Rule. See ValidationController.
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (strtoupper($value) !== $value) {
            $fail('The :attribute must be uppercase.');

            // Translate: $fail('validation.uppercase')->translate()
            // translate(['value' => $this->value], 'fr');
        }
    }

    //* Accessing all Data:
    // implements DataAwareRule interface, Define $data attribute
    // Then do it:
    public function setData(array $data): static
    {
        $this->data = $data;
        return $this;
    }

    //* Accessing Validator Instance:
    // implements DataAwareRule interface, Define $validator attribute
    // Then do it:
    public function setValidator(Validator $validator): static
    {
        $this->validator = $validator;
        return $this;
    }

    //* Access:
    // Call in controller like that:
    // $request->validate(['name' => ['required', new UpperCase]]);
    // Rather than custom rule, we can directly do in controller using closure if rule is not applied in many places:
    // $request->validate(['name' => ['required',  function (string $attribute, mixed $value, Closure $fail) {})]]);

    //* Implicit Rules:
    // If attribute is empty string or null, this custom rule wont be applied, but if we want:
    // php artisan make:rule Uppercase --implicit
}