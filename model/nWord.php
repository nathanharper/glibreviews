<?php
/**
 * Word
 **/
require_once('nDBModel.php');
require_once('nHyphen.php');
class nWord extends nDBModel {
    public $word;
    public $score;

    private $syllables;

    public static function get_table() {
        return 'word';
    }

    public function syllabize() {
        if (is_null($this->syllables)) {
            $syllables = nHyphen::syllabize($this->word);
            $this->syllables = !empty($syllables->{$this->word}) ? $syllables->{$this->word} : false;
        }
        return $this->syllables;
    }

    public function __get($name) {
        if ('syllables' == $name) {
            return $this->syllabize();
        }
        return NULL;
    }
}
