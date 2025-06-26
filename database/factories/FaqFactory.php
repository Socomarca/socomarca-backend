<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Faq>
 */
class FaqFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $questions = [
            '¿Cómo puedo realizar un pedido?',
            '¿Cuáles son los métodos de pago disponibles?',
            '¿Cuánto demora el envío?',
            '¿Puedo cambiar o cancelar mi pedido?',
            '¿Cómo puedo hacer seguimiento a mi pedido?',
            '¿Tienen garantía los productos?',
            '¿Realizan envíos a todo el país?',
            '¿Qué pasa si el producto llega dañado?',
            '¿Cómo puedo contactar con atención al cliente?',
            '¿Ofrecen descuentos o promociones?',
        ];

        $answers = [
            'Para realizar un pedido, simplemente navega por nuestro catálogo, selecciona los productos que deseas, agrégalos al carrito y procede con el checkout. Necesitarás registrarte o iniciar sesión para completar la compra.',
            'Aceptamos tarjetas de crédito, débito, transferencias bancarias y Webpay. Todos los pagos son procesados de forma segura.',
            'El tiempo de envío varía según tu ubicación. Generalmente, los envíos dentro de la región metropolitana toman 1-2 días hábiles, mientras que a regiones puede tomar 3-5 días hábiles.',
            'Puedes modificar o cancelar tu pedido dentro de las primeras 2 horas después de haberlo realizado. Después de este tiempo, contacta a nuestro equipo de atención al cliente.',
            'Una vez que tu pedido sea despachado, recibirás un email con el número de seguimiento para que puedas rastrear tu envío.',
            'Todos nuestros productos cuentan con garantía del fabricante. La duración varía según el tipo de producto.',
            'Sí, realizamos envíos a todo Chile. Los costos de envío se calculan automáticamente según tu ubicación.',
            'Si tu producto llega dañado, contacta inmediatamente a nuestro equipo de atención al cliente. Te ayudaremos con el cambio o reembolso.',
            'Puedes contactarnos a través del formulario en nuestro sitio web, por email o por teléfono. Nuestro horario de atención es de lunes a viernes de 9:00 a 18:00.',
            'Regularmente ofrecemos promociones y descuentos especiales. Suscríbete a nuestro newsletter para estar al tanto de las mejores ofertas.',
        ];

        $questionIndex = array_rand($questions);
        
        return [
            'question' => $questions[$questionIndex],
            'answer' => $answers[$questionIndex],
        ];
    }
}
