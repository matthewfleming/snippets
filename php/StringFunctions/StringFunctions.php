<?php

namespace github\matthewfleming\snippets\php\StringFunctions;

/**
 * String functions
 *
 * @author matthewfl
 */
class StringFunctions
{
    /**
     * Returns the given string in proper case, unless it already contains a mix of upper and lower case letters, in
     * which case it returns the string unchanged.
     * @param \string $string
     * @return \string
     */
    static function nameProperCaseFilter($string) {

        if(preg_match('/\p{Lu}/', $string) && preg_match('/\p{Ll}/', $string)) {
            return $string;
        }
        return self::nameProperCase($string);
    }

    /**
     * Return a proper case version of a given string which contains a name
     *
     * @param string $string
     * @return string
     */
    public static function nameProperCase($string)
    {
        $matches = array();

        $lower = strtolower($string);

        // tokenize "words" around characters that are not letters (whitespace & symbols - including '-. characters
        $words = preg_split('/(\P{L}+)/', $lower, -1, PREG_SPLIT_DELIM_CAPTURE);
        $length = count($words);

        // replace words with ucfirst copy (evens are "words", odds are a string of non-word characters)
        for ($i = 0; $i < $length; $i += 2) {
            $word = $words[$i];

            // prevent Bob's becoming Bob'S
            if ($word === 's' && $i > 0 && preg_match("/'$/", $words[$i - 1])) {
                continue;
            }

            // support McSomethings
            if (preg_match('/mc(.*)/', $word, $matches)) {
                $words[$i] = 'Mc' . ucfirst($matches[1]);
                continue;
            }

            $words[$i] = ucfirst($word);
        }

        // implode with original word seperators
        return implode('', $words);
    }
}