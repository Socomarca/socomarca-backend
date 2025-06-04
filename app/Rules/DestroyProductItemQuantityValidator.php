<?php

namespace App\Rules;

use App\Models\CartItem;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Auth;

class DestroyProductItemQuantityValidator implements ValidationRule
{
    public function __construct(private $productId, private string $unit)
    {
    }

    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $item = CartItem::where('user_id', Auth::user()->id)
            ->where('product_id', $this->productId)
            ->where('unit', $this->unit)
            ->first();

        if (!$item) {
            return;
        }

        if ($item->quantity < $value) {
            $fail("Product item quantity is less than the provided quantity to delete");
        }
    }
}
