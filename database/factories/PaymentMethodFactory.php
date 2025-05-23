<?php

namespace Database\Factories;

use App\Models\PaymentMethod;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PaymentMethod>
 */
class PaymentMethodFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
        // $methods = [
        //     ['name' => 'Transbank', 'active' => true],
        //     ['name' => 'Servipag', 'active' => true],
        //     ['name' => 'MercadoPago', 'active' => true],
        //     ['name' => 'PayPal', 'active' => false],
        //     ['name' => 'Stripe', 'active' => false],
        // ];

    protected $model = PaymentMethod::class;

    public function definition()
    {
        return [
            'name' => 'Manual', // se sobrescribirá en el seeder
            'active' => true,
        ];
    }
}
