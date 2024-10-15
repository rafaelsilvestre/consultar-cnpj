<?php

use Illuminate\Support\Arr;

if ( ! function_exists('verifyCPF')) {
    function verifyCPF(string $cpf)
    {
        $cpf = preg_replace('/\D/', '', $cpf);

        if (mb_strlen($cpf) != 11) {
            return false;
        }

        if (preg_match('/(\d)\1{10}/', $cpf)) {
            return false;
        }

        for ($t = 9; $t < 11; $t++) {
            for ($d = 0, $c = 0; $c < $t; $c++) {
                $d += $cpf[$c] * (($t + 1) - $c);
            }

            $d = ((10 * $d) % 11) % 10;

            if ($cpf[$c] != $d) {
                return false;
            }
        }

        return true;
    }
}

if ( ! function_exists('verifyCNPJ')) {
    function verifyCNPJ(string $cnpj)
    {
        $cnpj = preg_replace('/\D/', '', $cnpj);

        if (mb_strlen($cnpj) != 14) {
            return false;
        }

        if (preg_match('/(\d)\1{13}/', $cnpj)) {
            return false;
        }

        for ($i = 0, $j = 5, $sum = 0; $i < 12; $i++) {
            $sum += $cnpj[$i] * $j;
            $j = ($j == 2) ? 9 : $j - 1;
        }

        $rest = $sum % 11;

        if ($cnpj[12] != ($rest < 2 ? 0 : 11 - $rest)) {
            return false;
        }

        for ($i = 0, $j = 6, $sum = 0; $i < 13; $i++) {
            $sum += $cnpj[$i] * $j;
            $j = ($j == 2) ? 9 : $j - 1;
        }

        $rest = $sum % 11;

        return $cnpj[13] == ($rest < 2 ? 0 : 11 - $rest);
    }
}

if ( ! function_exists('exceptionToArray')) {
    function exceptionToArray($exception)
    {
        return [
            'message' => $exception->getMessage(),
            'exception' => get_class($exception),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'trace' => collect($exception->getTrace())->map(fn ($trace) => Arr::except($trace, ['args']))->all(),
        ];
    }
}

if ( ! function_exists('mask')) {
    function mask(string $string, string $mask)
    {
        $masked = '';

        $k = 0;
        for ($i = 0; $i <= mb_strlen($mask) - 1; ++$i) {
            if ($mask[$i] == '#') {
                if (isset($string[$k])) {
                    $masked .= $string[$k++];
                }
            } else {
                if (isset($mask[$i])) {
                    $masked .= $mask[$i];
                }
            }
        }

        return $masked;
    }
}
