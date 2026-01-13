<?php

namespace App\Helpers;

class NumberToWords
{
    private static $ones = [
        '', 'egy', 'kettő', 'három', 'négy', 'öt', 'hat', 'hét', 'nyolc', 'kilenc'
    ];

    private static $tens = [
        '', '', 'húsz', 'harminc', 'negyven', 'ötven', 'hatvan', 'hetven', 'nyolcvan', 'kilencven'
    ];

    private static $teens = [
        'tíz', 'tizenegy', 'tizenkettő', 'tizenhárom', 'tizennégy', 'tizenöt', 'tizenhat', 'tizenhét', 'tizennyolc', 'tizenkilenc'
    ];

    public static function convert($number)
    {
        $number = (int) $number;
        
        if ($number === 0) {
            return 'nulla';
        }

        if ($number < 0) {
            return 'mínusz ' . self::convert(abs($number));
        }

        $words = '';

        if ($number >= 1000000) {
            $millions = floor($number / 1000000);
            $words .= self::convertHundreds($millions) . ' millió';
            if ($millions > 1) {
                $words .= '';
            }
            $words .= ' ';
            $number %= 1000000;
        }

        if ($number >= 1000) {
            $thousands = floor($number / 1000);
            $words .= self::convertHundreds($thousands) . ' ezer ';
            $number %= 1000;
        }

        if ($number > 0) {
            $words .= self::convertHundreds($number);
        }

        return trim($words);
    }

    private static function convertHundreds($number)
    {
        $words = '';

        if ($number >= 100) {
            $hundreds = floor($number / 100);
            if ($hundreds === 1) {
                $words .= 'száz';
            } else {
                $words .= self::$ones[$hundreds] . 'száz';
            }
            $number %= 100;
        }

        if ($number >= 20) {
            $tens = floor($number / 10);
            $words .= self::$tens[$tens];
            $number %= 10;
        } elseif ($number >= 10) {
            $words .= self::$teens[$number - 10];
            $number = 0;
        }

        if ($number > 0) {
            $words .= self::$ones[$number];
        }

        return $words;
    }
}
