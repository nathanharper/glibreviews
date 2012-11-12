#!/usr/bin/php
<?php
require_once(realpath(dirname(__FILE__) . "/../settings.php"));
require_once(realpath(dirname(__FILE__) . "/../include/nRhyme.php"));

$result = nRhyme::process_title("beasts of the southern wild");
var_dump($result);
