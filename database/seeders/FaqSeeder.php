<?php

namespace Database\Seeders;

use App\Models\Faq;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class FaqSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faqs = [
            [
                'question' => '¿Cómo puedo realizar un pedido?',
                'answer' => 'Para realizar un pedido, simplemente navega por nuestro catálogo, selecciona los productos que deseas, agrégalos al carrito y procede con el checkout. Necesitarás registrarte o iniciar sesión para completar la compra. Una vez completado el proceso, recibirás un email de confirmación con los detalles de tu pedido.'
            ],
            [
                'question' => '¿Cuáles son los métodos de pago disponibles?',
                'answer' => 'Aceptamos múltiples métodos de pago para tu comodidad: tarjetas de crédito y débito (Visa, MasterCard, American Express), transferencias bancarias, y Webpay Plus. Todos los pagos son procesados de forma segura mediante conexiones encriptadas para proteger tu información financiera.'
            ],
            [
                'question' => '¿Cuánto demora el envío?',
                'answer' => 'El tiempo de envío varía según tu ubicación geográfica. Generalmente, los envíos dentro de la Región Metropolitana toman 1-2 días hábiles, mientras que a regiones puede tomar 3-5 días hábiles. Para zonas extremas, el tiempo puede extenderse a 7-10 días hábiles. Te notificaremos por email cuando tu pedido sea despachado.'
            ],
            [
                'question' => '¿Puedo cambiar o cancelar mi pedido?',
                'answer' => 'Puedes modificar o cancelar tu pedido dentro de las primeras 2 horas después de haberlo realizado, siempre que no haya sido procesado aún. Después de este tiempo, contacta inmediatamente a nuestro equipo de atención al cliente para evaluar si es posible realizar cambios. Una vez que el pedido esté en proceso de preparación o envío, no será posible realizar modificaciones.'
            ],
            [
                'question' => '¿Cómo puedo hacer seguimiento a mi pedido?',
                'answer' => 'Una vez que tu pedido sea despachado, recibirás automáticamente un email con el número de seguimiento y un enlace para rastrear tu envío en tiempo real. También puedes consultar el estado de tu pedido iniciando sesión en tu cuenta de usuario, donde encontrarás un historial completo de todas tus compras y su estado actual.'
            ],
            [
                'question' => '¿Tienen garantía los productos?',
                'answer' => 'Todos nuestros productos cuentan con garantía del fabricante, cuya duración varía según el tipo de producto. Además, ofrecemos 30 días de garantía de satisfacción: si no estás completamente satisfecho con tu compra, puedes devolverla en perfecto estado para obtener un reembolso completo o cambio por otro producto equivalente.'
            ],
            [
                'question' => '¿Realizan envíos a todo el país?',
                'answer' => 'Sí, realizamos envíos a todo Chile continental, incluyendo zonas extremas. Los costos de envío se calculan automáticamente según tu ubicación y el peso del pedido. Para algunas zonas remotas, puede aplicar un recargo adicional por envío especial. Las tarifas exactas se mostrarán durante el proceso de checkout antes de confirmar tu compra.'
            ],
            [
                'question' => '¿Qué pasa si el producto llega dañado?',
                'answer' => 'Si tu producto llega dañado o defectuoso, contacta inmediatamente a nuestro equipo de atención al cliente dentro de las 48 horas siguientes a la recepción. Te solicitaremos fotos del daño y procederemos con la devolución gratuita y envío de un producto de reemplazo sin costo adicional. Tu satisfacción es nuestra prioridad.'
            ],
            [
                'question' => '¿Cómo puedo contactar con atención al cliente?',
                'answer' => 'Puedes contactarnos a través de múltiples canales: formulario de contacto en nuestro sitio web, email a soporte@socomarca.cl, teléfono +56 2 2345 6789, o chat en vivo disponible en nuestro sitio. Nuestro horario de atención es de lunes a viernes de 9:00 a 18:00 hrs, y sábados de 9:00 a 13:00 hrs.'
            ],
            [
                'question' => '¿Ofrecen descuentos o promociones?',
                'answer' => 'Regularmente ofrecemos promociones especiales, descuentos por volumen, ofertas de temporada y cupones exclusivos para nuestros clientes registrados. Suscríbete a nuestro newsletter para recibir las mejores ofertas directamente en tu email, y síguenos en redes sociales para estar al tanto de promociones flash y descuentos especiales.'
            ],
            [
                'question' => '¿Puedo comprar sin registrarme?',
                'answer' => 'Para completar una compra es necesario registrarse en nuestro sistema, ya que necesitamos tus datos para el procesamiento del pedido, facturación y envío. Sin embargo, el proceso de registro es rápido y sencillo, requiriendo solo información básica. Una vez registrado, podrás realizar compras futuras de manera más rápida y acceder a tu historial de pedidos.'
            ],
            [
                'question' => '¿Cómo funciona el sistema de favoritos?',
                'answer' => 'Puedes agregar productos a tu lista de favoritos haciendo clic en el ícono de corazón en cada producto. Esto te permite guardar artículos de interés para comprar más tarde, comparar productos, y crear listas personalizadas. Tus favoritos se mantienen guardados en tu cuenta y puedes acceder a ellos en cualquier momento desde el menú de usuario.'
            ]
        ];

        foreach ($faqs as $faq) {
            Faq::create($faq);
        }
    }
}
