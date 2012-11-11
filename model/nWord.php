<?php
/**
 * Word
 **/
require_once('nDBModel.php');
class nWord extends nDBModel {
    public $word;
    public $score;

    public static function get_table() {
        return 'word';
    }
}
