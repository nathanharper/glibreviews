<?php
/**
 * Glib Name
 **/
require_once('nDBModel.php');

class nGlibName extends nDBModel {

    public $name;
    public $score;

    public static get_table() {
        return 'glib_name';
    }

}
