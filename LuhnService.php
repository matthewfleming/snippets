<?php

namespace github\matthewfleming\snippets;
/**
 * A class providing lunh checksum utilities
 * @author Matthew Fleming
 */
class LuhnService
{

    /**
     * Given an integer or string number, returns the luhn checksum digit
     * @param mixed $value
     * @return int
     */
    public static function generate($value)
    {
        if (null === $value || '' === $value) {
            throw new \Exception('$value must not be empty');
        }
        if (!is_numeric($value)) {
            throw new \Exception('$value must be numeric: ' . $value);
        }
        if (is_int($value)) {
            $value = '' . $value;
        }
        $value .= '0';
        $length = strlen($value);
        $oddLength = $length % 2;
        for ($sum = 0, $i = $length - 1; $i >= 0; $i--) {
            $digit = (int) $value[$i];
            $sum += (($i % 2) === $oddLength) ? array_sum(str_split($digit * 2)) : $digit;
        }
        return (10 - ($sum % 10)) % 10;
    }

    /**
     * Given an integer or string number, returns the string number with the luhn checksum appended
     * @param type $value
     * @return type
     */
    public static function appendLuhn($value)
    {
        return $value . self::generate($value);
    }

    /**
     *
     * Returns true if the given string has a valid lunh checksum
     * @param string $value
     * @return bool
     */
    public static function validate($value)
    {
        if (null === $value || '' === $value) {
            throw new \Exception('$value must not be empty');
        }
        if (!is_numeric($value)) {
            throw new \Exception('$value must be numeric: ' . $value);
        }
        $length = strlen($value);
        $oddLength = $length % 2;
        for ($sum = 0, $i = $length - 1; $i >= 0; $i--) {
            $digit = (int) $value[$i];
            $sum += (($i % 2) === $oddLength) ? array_sum(str_split($digit * 2)) : $digit;
        }
        return ($sum !== 0 && ($sum % 10) === 0);
    }

}
