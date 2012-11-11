#!/usr/bin/php
<?php
require_once(realpath(dirname(__FILE__) . '/../settings.php'));

$descriptor = array(
    0 => fopen('php://stdin', 'r'),
    1 => fopen('php://stdout', 'w'),
    2 => fopen('php://stderr', 'w')
);

$cmd = sprintf("mysql %s -u %s -h %s -p%s",
    escapeshellarg(DB_NAME),
    escapeshellarg(DB_USER),
    escapeshellarg(DB_HOST),
    escapeshellarg(DB_PASSWORD)
);

proc_open($cmd, $descriptor, $pipes);
