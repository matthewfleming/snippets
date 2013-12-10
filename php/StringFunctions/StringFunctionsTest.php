<?php

namespace github\matthewfleming\snippets\php\StringFunctions;

include "StringFunctions.php";

$names = array(
    "" => "",
    " " => " ",
    " - . ' " => " - . ' ",
    " - . ' some.thing-a'me - . ' " => " - . ' Some.Thing-A'Me - . ' ",
    "JEAN-LUC PICARD" => "Jean-Luc Picard",
    "MILES O'BRIEN" => "Miles O'Brien",
    "WILLIAM RIKER" => "William Riker",
    "geordi la forge" => "Geordi La Forge",
    "bEvErly CRuSHeR" => "Beverly Crusher",
    "Mcdonald" => "McDonald",
    "d'angelo" => "D'Angelo",
    "d'angelo-mcdonald" => "D'Angelo-McDonald",
    "mcdonald-d'angelo" => "McDonald-D'Angelo",
    "Bob's Grill" => "Bob's Grill",
    "r.e. block & sons" => "R.E. Block & Sons"
);

foreach ($names as $in => $out) {
    $transformed = StringFunctions::nameProperCase($in);
    $matches = ($transformed === $out) ? 'pass' : 'fail';

    print '"' . $in . '": "' . $transformed .  '" (' . $matches . ")\n";
}