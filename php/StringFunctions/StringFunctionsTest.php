<?php

namespace github\matthewfleming\snippets\php\StringFunctions;

include "StringFunctions.php";

$names1 = array(
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
    "'Super' autos" => "'Super' Autos",
    "r.e. block & sons (inc.)" => "R.E. Block & Sons (Inc.)",
);

$names2 = array(
    "" => "",
    " " => " ",
    " - . ' " => " - . ' ",
    " - . ' some.thing-a'me - . ' " => " - . ' Some.Thing-A'Me - . ' ",
    "JEAN-LUC PICARD" => "Jean-Luc Picard",
    "MILES O'BRIEN" => "Miles O'Brien",
    "WILLIAM RIKER" => "William Riker",
    "geordi la forge" => "Geordi La Forge",
    "bEvErly CRuSHeR" => "bEvErly CRuSHeR",
    "Mcdonald" => "Mcdonald",
    "d'angelo" => "D'Angelo",
    "d'angelo-mcdonald" => "D'Angelo-McDonald",
    "mcdonald-d'angelo" => "McDonald-D'Angelo",
    "Bob's Grill" => "Bob's Grill",
    "'Super' autos" => "'Super' autos",
    "r.e. block & sons (inc.)" => "R.E. Block & Sons (Inc.)",
);

function nameProperCaseTest() {
    echo "---\nTesting nameProperCase\n---\n";
    global $names1;
    foreach ($names1 as $in => $out) {
        $transformed = StringFunctions::nameProperCase($in);
        $matches = ($transformed === $out) ? 'PASS' : 'FAIL';

        print '"' . $in . '": "' . $transformed .  '" <<' . $matches . ">>\n";
    }
}

function nameProperCaseFilterTest() {
    echo "---\nTesting nameProperCaseFilter\n---\n";
    global $names2;
    foreach ($names2 as $in => $out) {
        $transformed = StringFunctions::nameProperCaseFilter($in);
        $matches = ($transformed === $out) ? 'PASS' : 'FAIL';

        print '"' . $in . '": "' . $transformed .  '" <<' . $matches . ">>\n";
    }
}

nameProperCaseTest();
nameProperCaseFilterTest();

echo preg_match('/^\p{Lu}(\p{Ll}*|\p{Lu}*)$/', 'hii');
echo preg_match('/^\p{Lu}(\p{Ll}*|\p{Lu}*)$/', 'hII');
echo preg_match('/^\p{Lu}(\p{Ll}*|\p{Lu}*)$/', 'Hii');
echo preg_match('/^\p{Lu}(\p{Ll}*|\p{Lu}*)$/', 'HII');
echo preg_match('/^\p{Lu}(\p{Ll}*|\p{Lu}*)$/', 'HIiI');

echo preg_match('/^\p{Lt}*$/', 'HIiI');
echo preg_match('/^\p{Lt}*$/', 'Hiii');
