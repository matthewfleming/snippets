<?php

namespace MatthewFleming\PHP;

DEFINE('TRIM_LEFT', 0);
DEFINE('TRIM_RIGHT', 1);
DEFINE('TRIM_BOTH', 2);

/**
 * Trim all whitespace from the beginning and/or end of a string:
 *  - UTF multibyte spaces (e.g. \xC2\xA0)
 *  - Unicode whitespace \p{Z}
 *  - ASCII whitespace \s
 * @param string $string The string to trim
 * @param int $type TRIM_LEFT, TRIM_RIGHT, TRIM_BOTH
 * @return string The string with leading, trailing or leading & trailing whitespace removed
 */
function trim_all($string, $type = TRIM_BOTH)
{
    $whitespace = "\xC2[\x85\xA0]|\xE1\xA0\x8E|\xE2\x80[\x80-\x8D\xA8\xA9\xAF]|\xE2\x81[\x9F\xA0]|\xE3\x80\x80|\xEF\xBB\xBF|\\p{Z}|\\s";
    $leading = "^($whitespace)+";
    $trailing = "($whitespace)+$";
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
 *  - UTF multibyte spaces (e.g. \xC2\xA0)
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
 *  - UTF multibyte spaces (e.g. \xC2\xA0)
 *  - Unicode whitespace \p{Z}
 *  - ASCII whitespace \s
 * Trim UTF16 spaces \xC2\xA0, Unicode whitespace \p{Z}, ASCII whitespace \s from the end
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
 *  - UTF multibyte spaces (e.g. \xC2\xA0)
 *  - Unicode whitespace \p{Z}
 *  - ASCII whitespace \s
 * @param string $string The string to trim
 * @return string The string with leading & trailing whitespace removed
 */
function trim_both($string)
{
    return trim_all($string, TRIM_BOTH);
}
