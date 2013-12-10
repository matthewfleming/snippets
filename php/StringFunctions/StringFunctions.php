<?php

namespace github\matthewfleming\snippets\php\StringFunctions;

/**
 * String functions
 *
 * @author matthewfl
 */
class StringFunctions
{

    public static function nameProperCase($string)
    {
        // Strip leading and trailing whitespace
        $matches = array();

        $lower = strtolower($string);

        // tokenize "words" around characters that are not letters (whitespace & symbols - including '-. characters
        $words = preg_split("/(\P{L}+)/", $lower, -1, PREG_SPLIT_DELIM_CAPTURE);
        $length = count($words);

        // replace words with ucfirst copy (evens are "words", odds are tokens)
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