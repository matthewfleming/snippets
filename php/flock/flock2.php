<?php
$fp = fopen('lock.txt', 'r+');

if (!$fp) {
    echo 'Unable to open file';
}

/* Activate the LOCK_NB option on an LOCK_EX operation */
if(!flock($fp, LOCK_EX | LOCK_NB)) {
    echo 'Unable to obtain lock';
    exit(-1);
}