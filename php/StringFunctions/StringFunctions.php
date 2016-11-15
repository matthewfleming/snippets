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
     * Dictionary 0123456789DFGHJKLMNPQRTVWXYZ, excludes ABCEIOSU
     * @var type
     */
    private static $base28SafetyMap = [
        'a' => 'D', 'b' => 'F', 'c' => 'G', 'd' => 'H', 'e' => 'J', 'f' => 'K', 'g' => 'L', 'h' => 'M', 'i' => 'N', 'j' => 'P',
        'k' => 'Q', 'l' => 'R', 'm' => 'T', 'n' => 'V', 'o' => 'W', 'p' => 'X', 'q' => 'Y', 'r' => 'Z'
    ];
    private $search;
    private $replace;

    public function __construct()
    {
        $this->search = array_reverse(array_keys(self::$base28SafetyMap));
        $this->replace = array_reverse(array_values(self::$base28SafetyMap));
    }

    /**
     * Returns the given string in proper case, unless it already contains a mix of upper and lower case letters, in
     * which case it returns the string unchanged.
     * @param \string $string
     * @return \string
     */
    static function nameProperCaseFilter($string)
    {

        if (preg_match('/\p{Lu}/', $string) && preg_match('/\p{Ll}/', $string)) {
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

    public function numberToSafeString($number, $padLength = 0)
    {
        if (!is_int($number) || !is_int($padLength)) {
            throw new \Exception("Parameters number '$number' and padlength '$padLength' must be integers");
        }

        $base28 = base_convert('' + $number, 10, 28);
        $safe28 = str_replace($this->search, $this->replace, $base28);

        if (strlen($safe28 < $padLength)) {
            return sprintf('%0' . $padLength . 's', $safe28);
        }
        return $safe28;
    }

}
