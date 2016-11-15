<?php

require_once "StringFunctions/StringFunctions.php";
require_once "crypto.php";

generateRandomCodes(6);

function generateRandomCodes($length)
{
    $min = pow(28, $length - 1);
    $max = 28 * $min - 1;

    for ($i = 0; $i < 28; $i++) {
        $number = psuedo_random_number($min, $max);
        $stringFunctions = new \github\matthewfleming\snippets\php\StringFunctions\StringFunctions;
        echo $stringFunctions->numberToSafeString($number), "\n";
    }
}
