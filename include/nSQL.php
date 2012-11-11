<?php
class nSQL {

    private static $db;

    public static function connect() {
        if (is_null(static::$db)) {
            static::$db = mysqli_connect(
                DB_HOST,
                DB_USER,
                DB_PASSWORD,
                DB_NAME
            );
        }
        return static::$db;
    }

    public static function query($sql) {
        return static::connect()->query($sql);
    }

    public static function escape($string) {
        return static::connect()->real_escape_string($string);
    }
}
