<?php
/**
 * Glib Name
 **/
require_once('nDBModel.php');

class nGlibName extends nDBModel {

    public $name;
    public $movie_id;
    public $score;

    public static function get_table() {
        return 'glib_name';
    }

    public function get_words() {
        if (!$this->get_id()) return false;
        return nWord::load(sprintf(
            "SELECT w.* FROM word w
            INNER JOIN glib_name_word gnw ON gnw.word_id = w.id
            WHERE gnw.glib_name_id = %d", $this->get_id()
        ));
    }

    /**
     * "Vote" for this glib name.
     * impart points to both the name and its constituent words.
     * @param int $points, the number of points to give the title
     * @param int $word_points, the number of points to give each word
     **/
    public function upvote($points=10, $word_points=10) {
        $this->score += $points;
        if ($this->save() && ($words = $this->get_words())) {
            foreach ($words as $word) {
                $word->score += $word_points;
                $word->save();
            }
        }
    }

}
