<?php
/**
 * Interface with Python Hyphenator script
 **/
class nHyphen {

    /**
     * "syllabize" a word or array of words.
     * if successful, returns a decoded json object:
     * { 'word' : ['sy', 'la', 'bles] }
     **/
    public static function syllabize($words) {
        if (is_string($words)) $words = array($words);

        $cmd = PYTHON_PATH . " " . HYPHENATOR . " " . implode(' ', array_map('escapeshellarg', $words));
        exec($cmd, $out, $code);

        if ($code) {
            return $code;
        }
        elseif (($json = json_decode($output)) === false) {
            return 'invalid json';
        }

        return $json;
    }
}
