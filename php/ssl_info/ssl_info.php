<?php
$g = stream_context_create (array(
    "ssl" => array(
        "capture_peer_cert" => true,
    ),
    'http' => array(
        'proxy' => 'proxy3.wanews.com.au:8000',
        'request_fulluri' => true,
    )
));
$r = fsockopen("ssl://ics2wstest.ic3.com:443", $errno, $errstr, 30,
    STREAM_CLIENT_CONNECT, $g);
$cont = stream_context_get_params($r);
var_dump($cont["options"]["ssl"]["peer_certificate"]);