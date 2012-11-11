<?php
/**
 * Movie
 **/
require_once('nDBModel.php');
class nMovie extends nDBModel {
    public $name;
    public $rt_id;
    public $release_date;

    public function generate_names() {
        echo 'not implemented';
    }

    public static function get_table() {
        return 'movie';
    }
}
