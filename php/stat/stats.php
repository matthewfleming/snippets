<?php

define('WAIT', 10);

function doStats(&$stats)
{
    clearstatcache();
    if (!file_exists(FILENAME)) {
        echo "File does not exist\n";
        return;
    }
    $stat = stat(FILENAME);
    $oldStat = end($stats);
    if ($oldStat) {
        foreach ($stat as $key => $val) {
            if (is_string($key)) {
                $diff = $val - $oldStat[$key];
                if ($diff) {
                    echo "$key: $diff\n";
                }
            }
        }
    } else {
        print_r($stat);
    }
    $stats[] = $stat;
}
