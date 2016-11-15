<?php

$fp = fopen("lock.txt", "r+");

if (!$fp) {
    echo 'Unable to open file';
}

if (flock($fp, LOCK_EX)) {  // acquire an exclusive lock
    fwrite($fp, "FLOCK1: lock obtained\n");
    fflush($fp);            // flush output before releasing the lock
    sleep(10);
    flock($fp, LOCK_UN);    // release the lock
} else {
    echo "Couldn't get the lock!";
}

fclose($fp);
