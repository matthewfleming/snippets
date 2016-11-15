<?php

require 'stats.php';

define('FILENAME', '\\\\dtiapp01\Invoices\Processing\test.csv');

$stats = [];

echo "opening file\n";
$fp = fopen(FILENAME, "w+");

if (!$fp) {
    echo 'Unable to open file';
    exit;
}
echo "after opening file\n";
doStats($stats);

echo "sleeping\n";
sleep(WAIT);

echo "writing to file\n";
fseek($fp, 0, SEEK_END);
fwrite($fp, "stat1.php writing to file");
doStats($stats);

echo "sleeping\n";
sleep(WAIT);

echo "flushing file\n";
fflush($fp);
doStats($stats);

echo "sleeping\n";
sleep(WAIT);

echo "closing file\n";
fclose($fp);
doStats($stats);

echo "removing file\n";
unlink(FILENAME);
