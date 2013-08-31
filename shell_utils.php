<?php

function echoTitle($title, $length = null)
{
    if($length === null) {
        $length = strlen($title);
    }
    for($i=0;$i<$length;$i++) {
        echo '-';
    }
    echo "\n";
    echo $title;
    echo "\n";
    for($i=0;$i<$length;$i++) {
        echo '-';
    }
    echo "\n";
}