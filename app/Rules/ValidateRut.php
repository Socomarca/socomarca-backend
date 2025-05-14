<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidateRut implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // Eliminar puntos y guión
        $rut = preg_replace('/[^k0-9]/i', '', $value);
        
        // Obtener dígito verificador
        $dv = substr($rut, -1);
        
        // Obtener cuerpo del RUT (sin dígito verificador)
        $numero = substr($rut, 0, -1);
        
        // Validaciones básicas
        if (empty($numero)) {
            $fail('El RUT no puede estar vacío.');
            return;
        }
        
        // Calcular dígito verificador
        $suma = 0;
        $multiplo = 2;
        
        // Recorrer cada dígito de derecha a izquierda
        for ($i = strlen($numero) - 1; $i >= 0; $i--) {
            $suma += $numero[$i] * $multiplo;
            $multiplo = $multiplo < 7 ? $multiplo + 1 : 2;
        }
        
        $dvEsperado = 11 - ($suma % 11);
        
        // Convertir a formato esperado
        if ($dvEsperado == 11) {
            $dvEsperado = '0';
        } elseif ($dvEsperado == 10) {
            $dvEsperado = 'K';
        } else {
            $dvEsperado = (string) $dvEsperado;
        }
        
        // Comparar dígito verificador
        if (strtoupper($dv) != strtoupper($dvEsperado)) {
            $fail('El RUT ingresado no es válido.');
        }
    }
}
