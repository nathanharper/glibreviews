<?php
/**
 * Example settings file
 **/
define('RT_KEY', 'blahblah'); # Rotten Tomatoes API key
define('TESTING_MODE', TRUE);

# mySQL credentials
define('DB_NAME', 'glibreviews');
define('DB_HOST', 'localhost');
define('DB_USER', 'glibuser');
define('DB_PASSWORD', 'blah blah blah');

define('PYTHON_PATH', '/usr/bin/python');
define('HYPHENATOR', realpath(dirname(__FILE__) . '/scripts/hyphenate.py'));
