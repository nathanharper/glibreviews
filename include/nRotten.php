<?php
/**
 * This class connects to the Rotten Tomatoes API
 * to retrieve movie data.
 **/
require_once('nSQL.php');
class nRotten {

    private static $base_url = "http://api.rottentomatoes.com/api/public/v1.0";

    /**
     * Retrieve a list of Opening Movies
     * that are not already in the database
     **/
    public static function get_opening_movies($limit = 20) {
        try {
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_URL, static::$base_url . "/lists/movies/opening.json?apikey=" . RT_KEY . "&limit=$limit");
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

            $json = curl_exec($curl);
            curl_close($curl);

            if (($json = json_decode($json)) !== NULL) {
                $db = nSQL::connect();
                foreach ($json->movies as $movie) {

                    # check to see if this movie is already in our DB
                    $result = $db->query(sprintf(
                        "SELECT id FROM movie WHERE rt_id = %d",
                        $movie->id
                    ));

                    # we have no record of this film. add it to the database and calculate glib names
                    if (!$result->num_rows) {
                        $movie_object = new nMovie(array(
                            'name' => $movie->title,
                            'rt_id' => $movie->id,
                            'release_date' => (isset($movie->release_dates->theater) ? strtotime($movie->release_dates->theater) : time()),
                        ));

                        if ($movie_object->save()) {
                            $movie_object->generate_names();
                        }
                    }
                }
            }
        }
        catch (Exception $e) {
            error_log(print_r($e, true));
        }

        return NULL;
    }
}
