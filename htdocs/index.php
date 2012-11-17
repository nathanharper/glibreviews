<?php
date_default_timezone_set('America/New_York');
define('SITE_ROOT', realpath(dirname(__FILE__) . "/.."));
set_include_path(SITE_ROOT . "/include");
require_once(SITE_ROOT . "/settings.php");

# Load up all Model objects
$model_dir = opendir(SITE_ROOT . "/model");
while (FALSE !== ($file = readdir($model_dir))) {
    if ('n' == substr($file, 0, 1) && '.php' == substr($file, -4)) {
        include(SITE_ROOT . "/model/$file");
    }
}
closedir($model_dir);

require_once('nRhyme.php');
$result = nRhyme::process_title('Insidious');
var_dump($result);
