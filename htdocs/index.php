<?php
date_default_timezone_set('America/New_York');
define('SITE_ROOT', realpath(dirname(__FILE__) . "/.."));
set_include_path(SITE_ROOT . "/include");
require_once(SITE_ROOT . "/settings.php");

# Load up all Model objects
$model_dir = opendir(SITE_ROOT . "/model");
while (FALSE !== ($file = readdir($model_dir))) {
    if ('n' == substr($file, 0, 1) && '.php' == substr($file, -4)) {
        require_once(SITE_ROOT . "/model/$file");
    }
}
closedir($model_dir);

require_once('nRhyme.php');

$title = !empty($_GET['title']) ? $_GET['title'] : "Lord of the Rings";
$names_per_word = !empty($_GET['names']) ? intval($_GET['names']) : 10;
$rhymes_per_word = !empty($_GET['rhymes']) ? intval($_GET['rhymes']) : 10;

if ($title && ($result = nRhyme::process_title($_GET['title'], $names_per_word, $rhymes_per_word))) {
    foreach ($result as $data) {
        echo "{$data['title']}<br />";
    }
}
else {
    echo "<b>no results</b>";
}
