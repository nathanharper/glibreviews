<?php
/**
 * Movie
 **/
require_once('nDBModel.php');
require_once('nRhyme.php');

class nMovie extends nDBModel {
    public $name;
    public $rt_id;
    public $score;
    public $release_date;

    /**
     * Generate alternate titles for the film using RhymeBrain.
     * @param int $name_count, the number of names to generate
     **/
    public function generate_names($names_per_word=5, $rhymes_per_word=10) {

        if (!$this->name) return false;

        $names = nRhyme::process_title($this->name, $names_per_word, $rhymes_per_word);
        $db = nSQL::connect();

        if ($names)
        foreach ($names as $data) {
            if (nGlibName::load_one(array('name' => $data['title'], 'movie_id' => $this->get_id()))) {
                # glib title already exists
                continue;
            }

            $glib = new nGlibName(array(
                'name' => $data['title'],
                'movie_id' => $this->get_id()
            ));

            if ($glib->save()) {
                $word = nWord::load_one(array('word' => $data['rhyme']));
                if (!$word) {
                    $word = new nWord(array(
                        'word' => $data['rhyme']
                    ));
                    $word->save();
                }

                if ($word_id = $word->get_id()) {
                    $db->query(sprintf(
                        "INSERT INTO glib_name_word (glib_name_id, word_id) VALUES (%d, %d)",
                        $glib->get_id(),
                        $word_id
                    ));
                }
            }
        }

    }

    public static function get_table() {
        return 'movie';
    }
}
