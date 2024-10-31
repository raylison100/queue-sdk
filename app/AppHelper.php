<?php

declare(strict_types=1);

namespace App;

/**
 * Class Helper.
 */
class AppHelper
{
    /**
     * @param $string
     *
     * @return mixed
     */
    public static function removeSpecialCharacters($string)
    {
        return preg_replace('/[^A-Za-z0-9]/', '', $string);
    }

    /**
     * @param $string
     *
     * @return mixed
     */
    public static function removeAccentuation($string)
    {
        return preg_replace([
            '/(á|à|ã|â|ä)/',
            '/(Á|À|Ã|Â|Ä)/',
            '/(é|è|ê|ë)/',
            '/(É|È|Ê|Ë)/',
            '/(í|ì|î|ï)/',
            '/(Í|Ì|Î|Ï)/',
            '/(ó|ò|õ|ô|ö)/',
            '/(Ó|Ò|Õ|Ô|Ö)/',
            '/(ú|ù|û|ü)/',
            '/(Ú|Ù|Û|Ü)/',
            '/(ñ)/', '/(Ñ)/'
        ], explode(' ', 'a A e E i I o O u U n N'), $string);
    }

    /**
     * Price formatter.
     *
     * @param $value
     *
     * @return string
     */
    public static function formatPrice($value)
    {
        if (strstr($value, '.')) {
            $exp = explode('.', $value);

            if (1 == mb_strlen($exp[1])) {
                $decimal = $exp[1] . '0';
            } else {
                $decimal = $exp[1];
            }

            $price = $exp[0] . $decimal;
        } else {
            $price = $value . '00';
        }

        return $price;
    }

    /**
     * Insert blank spaces into string.
     *
     * @param $quantity
     *
     * @return string
     */
    public static function insertSpace($quantity)
    {
        $spaces = '';

        for ($i = 0; $i < $quantity; ++$i) {
            $spaces .= ' ';
        }

        return $spaces;
    }

    /**
     * Remove blank spaces into string.
     *
     * @param $value
     *
     * @return string
     */
    public static function removeSpaces($value)
    {
        return trim(str_replace(' ', '', $value));
    }

    /**
     * Insert characters to the left side of string.
     *
     * @param      $value
     * @param      $qtd
     * @param      $char
     * @param bool $custom
     *
     * @return string
     */
    public static function insertChar($value, $qtd, $char, $custom = false)
    {
        if (mb_strlen($value) > $qtd) {
            return substr($value, 0, $qtd);
        }

        $quantity = $qtd - mb_strlen($value);
        $return = '';

        for ($i = 0; $i < $quantity; ++$i) {
            $return .= $char;
        }

        if ($custom) {
            return $value . $return;
        }

        return $return . $value;
    }

    /**
     * Read array and return this values.
     *
     * @param $values
     *
     * @return string $result
     */
    public static function getValues($values)
    {
        return implode('', $values);
    }

    /**
     * Check if is a valid date.
     *
     * @param $date
     *
     * @return bool
     */
    public static function isValidDate($date)
    {
        return preg_match('/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/', $date);
    }

    /**
     * @param $date
     *
     * @return false|string
     */
    public static function formatDateDB($date)
    {
        return (self::isValidDate($date)) ? $date : date('Y-m-d', strtotime($date));
    }

    /**
     * Return age by date of birth.
     *
     * @param $dateBirth
     *
     * @return null|int
     */
    public static function getAgeByDateBirth($dateBirth)
    {
        return (empty($dateBirth)) ? null : date_diff(date_create($dateBirth), date_create('now'))->y;
    }
}
