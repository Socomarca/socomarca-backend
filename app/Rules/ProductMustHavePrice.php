<?php

namespace App\Rules;

use App\Models\Price;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ProductMustHavePrice implements ValidationRule
{
    public function __construct(private string $unit)
    {
    }

    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $price = Price::where('product_id', $value)
            ->where('unit', $this->unit)
            ->where('is_active', true)
            ->first();

        if (!$price) {
            $fail('The selected product does not have an active price.');
        }
    }
}
