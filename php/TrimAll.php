<?php

namespace MatthewFleming\PHP;

DEFINE('TRIM_LEFT', 0);
DEFINE('TRIM_RIGHT', 1);
DEFINE('TRIM_BOTH', 2);

/**
 * Trim all whitespace from the beginning and/or end of a string:
 *  - UTF multibyte spaces (e.g. \xc2\xa0)
 *  - Unicode whitespace \p{Z}
 *  - ASCII whitespace \s
 * @param string $string The string to trim
 * @param int $type TRIM_LEFT, TRIM_RIGHT, TRIM_BOTH
 * @return string The string with leading, trailing or leading & trailing whitespace removed
 */
function trim_all($string, $type = TRIM_BOTH)
{
    $mb = "\xc2\x85|\xc2\xa0|\xe1\xa0\x8e|\xe2\x80[\x80-\x8D]|\xe2\x80\xa8|\xe2\x80\xa9|\xe2\x80\xaF|\xe2\x81\x9f|\xe2\x81\xa0|\xe3\x80\x80|\xef\xbb\xbf";
    $all = $mb . '|\p{Z}|\s';
    $leading = "^($all)+";
    $trailing = "($all)+$";
    switch ($type) {
        case TRIM_LEFT:
            $pattern = $leading;
            break;
        case TRIM_RIGHT:
            $pattern = $trailing;
            break;
        case TRIM_BOTH:
            $pattern = "$leading|$trailing";
            break;
        default:
            trigger_error("Invalid TRIM type (TRIM_LEFT, TRIM_RIGHT, TRIM_BOTH)", E_USER_ERROR);
            return $string;
    }
    return preg_replace("/$pattern/", '', $string);
}

/**
 * Trim all whitespace from the beginning of a string:
 *  - UTF multibyte spaces (e.g. \xc2\xa0)
 *  - Unicode whitespace \p{Z}
 *  - ASCII whitespace \s
 * @param string $string The string to trim
 * @return string The string with leading whitespace removed
 */
function trim_left($string)
{
    return trim_all($string, TRIM_LEFT);
}

/**
 * Trim all whitespace from the end of a string:
 *  - UTF multibyte spaces (e.g. \xc2\xa0)
 *  - Unicode whitespace \p{Z}
 *  - ASCII whitespace \s
 * Trim UTF16 spaces \xc2\xa0, Unicode whitespace \p{Z}, ASCII whitespace \s from the end
 * of a string
 * @param string $string The string to trim
 * @return string The string with trailing whitespace removed
 */
function trim_right($string)
{
    return trim_all($string, TRIM_RIGHT);
}

/**
 * Trim all whitespace from the beginning & end of a string:
 *  - UTF multibyte spaces (e.g. \xc2\xa0)
 *  - Unicode whitespace \p{Z}
 *  - ASCII whitespace \s
 * @param string $string The string to trim
 * @return string The string with leading & trailing whitespace removed
 */
function trim_both($string)
{
    return trim_all($string, TRIM_BOTH);
}
