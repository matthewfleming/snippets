<?php

require 'stats.php';
define('FILENAME', '\\\\dtiapp01\Invoices\Processing');

$stats = [];
for ($i = 0; $i < 4; $i++) {
    echo "try $i\n";
    doStats($stats);
    sleep(WAIT);
}
