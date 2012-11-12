<?php
/**
 * Model class - Simple ORM stuff
 **/
require_once('nSQL.php');
abstract class nDBModel {

    private $id;

    public abstract static function get_table();

    public function __construct($result_set = array()) {
        if (is_array($result_set)) {
            foreach ($result_set as $property => $value) {
                $this->$property = $value;
            }
        }
    }

    public function get_id() {
        return $this->id;
    }

    /**
     * Load an instance or instances of this object from the database.
     * The parameter entered can be:
     * 1. a numeric id if you just want to load one object
     * 2. a SQL string or an array of column => value pairs, if you want to load an array of results
     **/
    public static function load($param='') {
        $class = get_called_class();
        $table = static::get_table();
        if (is_numeric($param)) {
            $result = nSQL::query(sprintf(
                "SELECT * FROM %s WHERE id = %d",
                $table,
                $param
            ));

            if ($result = $result->fetch_assoc()) {
                return new $class($result);
            }
            return false;
        }
        else {
            $db = nSQL::connect();
            if (is_string($param)) {
                if ($param == '*' || empty($param)) {
                    $param = sprintf("SELECT * FROM %s", $table);
                }
                $result = $db->query($param);
            }
            elseif (is_array($param)) {
                $cols = array();
                foreach ($param as $property => $value) {
                    $cols[] = sprintf("%s = '%s'", $property, $db->real_escape_string($value));
                }
                $result = $db->query(sprintf(
                    "SELECT * FROM %s WHERE %s",
                    $table,
                    implode(' AND ', $cols)
                ));
            }
            else {
                return false;
            }

            if (!empty($result) && $result->num_rows) {
                $result_array = array();
                while ($row = $result->fetch_assoc()) {
                    $result_array[] = new $class($row);
                }
                return $result_array;
            }
        }

        return false;
    }

    /**
     * save to database
     **/
    public function save() {

        $db = nSQL::connect();

        $vars = get_object_vars($this);
        $vars = array_filter($vars, function($a){return !empty($a)});

        if (!$vars) return false;

        if (!$this->id) {
            $properties = array_keys($vars);

            $sql = sprintf(
                "INSERT INTO %s (%s) VALUES (%s)",
                $this->get_table(),
                implode(', ', $properties),
                implode(', ', $vars)
            );

            if ($result = $db->query($sql)) {
                $this->id = $db->insert_id;
            }
        }
        else {
            $updates = array();
            foreach ($vars as $property => $value) {
                $updates[] = sprintf("%s = '%s'", $property, $db->real_escape_string($value));
            }

            $sql = sprintf(
                "UPDATE %s SET %s WHERE id = %d",
                $this->get_table(),
                implode(', ', $updates),
                $this->id
            );

            $result = $db->query($sql);
        }
        
        return $result;
            
    }
}

class nDBModelException extends Exception {
}
