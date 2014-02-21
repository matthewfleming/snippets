<?php

namespace github\matthewfleming\snippets;

require 'shell_utils.php';
require 'LuhnService.php';
/**
 * @author Matthew Fleming
 */

echoTitle('Generate and check valid luhns.');

$vals = array();
for ($i = 1; $i < 10; $i++) {
    $val = '';
    for($j=1;$j<=$i;$j++) {
        $val .= $i;
    }
    $val *= 13;
    $vals[] = $val;
    $luhn = LuhnService::appendLuhn($val);
    echo $luhn . ': ' . (LuhnService::validate($luhn) ? 'valid' : 'invalid') . "\n";
}

echoTitle('Generate and check invalid luhns');

foreach($vals as $val) {
    for($j=1;$j<10;$j++) {
        $luhn = $val . ((LuhnService::generate($val) + $j) % 10);
        echo $luhn . ': ' . (LuhnService::validate($luhn) ? 'valid' : 'invalid') . "\n";
    }
}