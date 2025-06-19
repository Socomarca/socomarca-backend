<?php

namespace App\Rules;

use App\Models\Price;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ProductHasUnit implements ValidationRule
{
    public function __construct(private $productId) {}
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($this->productId === null) {
            $fail("No product id provided for validation");
            return;
        }

        Price::where('product_id', $this->productId)
            ->where('unit', $value)
            ->exists()
            ?:
            $fail('The selected unit is not valid for this product');
    }
}
