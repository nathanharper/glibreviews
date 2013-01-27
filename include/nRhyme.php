<?php
/**
 * nRhyme class -- interfaces with RhymeBrain API
 **/
require_once('nSQL.php');
require_once(SITE_ROOT . '/model/nWord.php');

class nRhyme {

    private static $base_url = "http://rhymebrain.com/talk";
    private static $min_word_size = 3;

    private static $rhyme_full_words = true;
    private static $rhyme_syllables = false; # whether or not to rhyme word syllables by default

    # array of custom word scores so we don't keep 
    # making unecessary queries.
    private static $custom_word_scores = array(); 

    public static $nw = null; # Numbers_Words object

    # some words were just not meant to be rhymed. this is a list of them.
    # NOTE: no point adding words that are shorter than the min word size
    private static $boring_words = array(
        'the',
    );

    # convert a number to a word
    public static function num_to_word($num) {
        if (! is_null(static::$nw)) {
            return static::$nw->toWords(intval($num));
        }
        return $num;
    }

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
        # Turn numerals into words so we can rhyme them
        try {
            require_once('Numbers/Words.php');
            static::$nw = new Numbers_Words();
            $title = preg_replace("/\d+/e", 'static::num_to_word($0);', $title);
        } catch (Exception $e) {
            error_log("Could not translate numbers to words. make sure PEAR package Numbers_Words is properly installed.");
        }

        $parts = preg_split("/\W+/", $title, null, PREG_SPLIT_NO_EMPTY);

        if (!$parts) return false;
        $more_than_1 = (count($parts) > 1);
        $one_word = (count($parts) < 2);

        if ($one_word) {
            # replacing the only word in a title wouldn't make much sense.
            # split the word into syllables and try to rhyme them.
            static::$rhyme_syllables = TRUE;
            static::$rhyme_full_words = FALSE;
        }

        $rhymes = array();
        foreach ($parts as $word) {
            if (!isset($rhymes[$word]) && (static::is_rhymable($word) || $one_word)) {
                if (static::$rhyme_full_words) {
                    $word_rhymes = static::get_rhymes($word, $rhymes_per_word);
                    static::generate_titles($word_rhymes, $word, $title);
                    $rhymes[$word] = $word_rhymes;
                }
                if (static::$rhyme_syllables) {
                    $word_object = new nWord(array('word' => $word));
                    $word_rhymes = static::get_rhymes($word, $rhymes_per_word);

                    $syllable_count = count($word_object->syllables);
                    if ($syllable_count < 2) {
                        # word is one syllable
                        if (!static::$rhyme_full_words && $one_word) {
                            static::generate_titles($word_rhymes, $word, $title);
                            $rhymes[$word] = $word_rhymes;
                        }
                        continue; # if the full word has already been rhymed, we can continue
                    }

                    list($replace) = array_slice($word_object->syllables, -1);

                    # start at the end of the word and work backwards
                    # Still rhyme the full word, but replace only the last syllable --
                    # otherwise, RhymeBrain will often get the pronunciation wrong.
                    for ($i=1; $i < $syllable_count; $i++) {
                        if (!isset($rhymes[$replace])) {
                            static::generate_titles($word_rhymes, $replace, $title, TRUE);
                            $rhymes[$replace] = $word_rhymes;
                        }
                        list($next_part) = array_slice($word_object->syllables, 0 - ($i + 1), 1);
                        $replace = $next_part . $replace;
                    }
                }
            }
        }

        static::sort_by_score($rhymes, $names_per_word);

        $new_names = array();
        foreach ($rhymes as $word => $data) {
            foreach ($data as $rhyme => $stuff) {
                $new_names[] = array(
                    'title' => $stuff['glib_title'],
                    'rhyme' => $rhyme
                );
            }
        }

        return $new_names;
    }

    /**
     * generate titles for an array of words.
     *
     * @param array $replacements, array of rhyme words. This is directly modified.
     * @param string $word, the word to replace
     * @param string $title, the full original title
     * @param bool $syllables, whether or not to do replacements at syllable level
     **/
    public static function generate_titles(&$replacements, $word, $title, $syllables = FALSE) {
        if ($syllables) {
            $regex = "/\B%s\b/i";
        }
        else {
            $regex = "/\b%s\b/i";
        }

        foreach ($replacements as $rhyme_word => $rhyme_data) {
            if ($syllables && preg_match("/^[aeiouy]/i", $rhyme_word)) {
                # if the first letter of the rhyme is a vowel,
                # remove any vowels from the end of the word prior to the match
                $final_regex = '/[aeiouy]*\B\Q' . $word . '\E\b/i';
            }
            else {
                $final_regex = sprintf($regex, '\Q' . $word . '\E');
            }

            $replacements[$rhyme_word]['glib_title'] = preg_replace(
                $final_regex,
                $rhyme_word,
                $title
            );
        }
    }

    /**
     * determines if the provided word is fit to be rhymed
     **/
    public static function is_rhymable($word) {
        return (
            strlen($word) >= static::$min_word_size &&
            !in_array(strtolower($word), static::$boring_words)
        );
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
