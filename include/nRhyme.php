<?php
/**
 * nRhyme class -- interfaces with RhymeBrain API
 **/
require_once('nSQL.php');

class nRhyme {

    private static $base_url = "http://rhymebrain.com/talk";
    private static $min_word_size = 3;

    # array of custom word scores so we don't keep 
    # making unecessary queries.
    private static $custom_word_scores = array(); 

    # some words were just not meant to be rhymed. this is a list of them.
    private static $boring_words = array(
        'the',
    );

    /**
     * Split a movie title, retrieve rhymes, process results
     *
     * @param string title, the title of the film
     * @param int names_per_word, number of glib names to make for each viable rhyme word
     * @param int rhymes_per_word, number of rhymes to query for for each word of the title
     *
     * @return a $name_count length array of glib titles
     **/
    public static function process_title($title, $names_per_word=5, $rhymes_per_word=10) {
        $parts = preg_split("/\W+/", $title, null, PREG_SPLIT_NO_EMPTY);

        if (count($parts) > 1) {
            # For now, if we have more than one distinct word, don't bother separating 
            # by syllable. TODO: consider changing this in the future?
            $rhymes = array();
            foreach ($parts as $word) {
                if (!isset($rhymes[$word]) && strlen($word) >= static::$min_word_size && !in_array($word, static::$boring_words)) {
                    $rhymes[$word] = static::get_rhymes($word, $rhymes_per_word);
                }
            }

            static::sort_by_score($rhymes, $names_per_word);

            $new_names = array();
            foreach ($rhymes as $word => $data) {
                foreach ($data as $rhyme => $stuff) {
                    $new_names[] = array(
                        'title' => preg_replace("/\b\Q" . $word . "\E\b/", $rhyme, $title),
                        'rhyme' => $rhyme
                    );
                }
            }

            return $new_names;
        }
        else {
            # replacing the only word in a title wouldn't make much sense.
            # split the word into syllables and try to rhyme them.
            $word = $parts[0];
            # TODO: implement.
        }
    }

    /**
     * find rhymes for a word.
     *
     * @param string $word, the word to rhyme
     * @param int $count, the number of rhymes to get
     *
     * @return an array where the indices are the rhyming words and the values are the corresponding
     *  rhyme scores. the RhymeBrain API seems to sort by score automatically, so this script doesnt bother.
     **/
    public static function get_rhymes($word, $count = 10) {
        $ch = curl_init();

        # &maxResults=$count
        # NOTE: taking the above expression out of the url.
        # limiting results doesn't seem to retrieve the highest-scored rhymes...
        curl_setopt($ch, CURLOPT_URL, static::$base_url . "?function=getRhymes&word=$word");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $json = curl_exec($ch);
        curl_close($ch);

        if (($json = json_decode($json)) !== FALSE) {
            $return = array();
            $db = nSQL::connect();
            $current = 0;
            foreach ($json as $j) {
                if ($current == $count) break;

                if (isset(static::$custom_word_scores[$j->word])) {
                    $glib_score = static::$custom_word_scores[$j->word];
                }
                else {
                    # query to see if we have a score for this word
                    $sql = sprintf(
                        "SELECT score FROM word WHERE word = '%s' LIMIT 1",
                        $j->word
                    );

                    $glib_score = 0;
                    $result = $db->query($sql);
                    if ($result->num_rows) {
                        list($glib_score) = $result->fetch_row();
                    }

                    static::$custom_word_scores[$j->word] = $glib_score;
                }

                $return[$j->word] = array(
                    'score' => $j->score,
                    'glib_score' => $glib_score
                );

                $current++;
            }
            return $return;
        }

        return false;
    }

    /**
     * Given a data set of words and their rhymes,
     * determine the highest ranking substitutions.
     *
     * @param array $data, an array of data formatted like this:
     *      array(
     *          'word1' => array(
     *              'rhyme1-1' => 'rhyme1-1-score',
     *              'rhyme1-2' => 'rhyme1-2-score',
     *          ),
     *          'word2' => array(
     *              ........
     *
     * @return an array of highest ranked substitutions in order. example:
     *      array(
     *          array('word1', 'rhyme1', 'score1'),
     *          array('word2', 'rhyme2', 'score2'),
     *          ..........
     **/
    public static function sort_by_score(&$data, $max_results=0) {

        # var_dump($data);
        foreach ($data as $key => $info) {
            uasort(
                $data[$key],
                function ($a, $b) {
                    if ($a['score'] > $b['score']) return -1;
                    if ($b['score'] > $a['score']) return 1;
                    return 0;
                }
            );

            if ($max_results && count($data[$key]) > $max_results) {
                $data[$key] = array_slice($data[$key], 0, $max_results);
            }
        }

    }

}
