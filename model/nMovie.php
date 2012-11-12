<?php
/**
 * Movie
 **/
require_once('nDBModel.php');
require_once('nRhyme.php');

class nMovie extends nDBModel {
    public $name;
    public $rt_id;
    public $release_date;

    /**
     * Generate alternate titles for the film using RhymeBrain.
     * @param int $name_count, the number of names to generate
     **/
    public function generate_names($name_count = 5) {

        if (!$this->name) return false;

        $parts = preg_split("/\W+/", $this->name, null, PREG_SPLIT_NO_EMPTY);

        if (count($parts) > 1) {
            # For now, if we have more than one distinct word, don't bother separating 
            # by syllable. TODO: consider changing this in the future?
            $rhymes = array();
            foreach ($parts as $word) {
                $rhymes[$word] = nRhyme::get_rhymes($word, 20);
            }

            for ($i=0; $i < $name_count; $i++) {
            }
        }
        else {
            # replacing the only word in a title wouldn't make much sense.
            # split the word into syllables and try to rhyme them.
            $word = $parts[0];
        }
    }

    public static function get_table() {
        return 'movie';
    }
}
