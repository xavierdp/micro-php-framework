<?php
class x_Model
{
    static $db = "db0";
    // static $tableName = "test_test";

    public static function db()
    {
        $db = static::$db;

        return $db();
    }

    public static function load($a_in = null)
    {
        h(__METHOD__);
        pr($a_in);

        return static::db()->oQueryFetchArray("SELECT * FROM `" . static::$tableName . "`");
    }

    public static function insert($a_in = null)
    {
        h(__METHOD__);
        pr($a_in);

        unset($a_in["id"]);

        $id = static::db()->oInsert(static::$tableName, $a_in);

        $a_in["id"] = $id;

        return $a_in;
    }

    public static function update($a_in = null)
    {
        h(__METHOD__);

        pr($a_in);

        $id = static::db()->oInsertUpdate(static::$tableName, $a_in);

        $a_in["id"] = $id;

        return $a_in;
    }

    public static function delete($a_in = null)
    {
        h(__METHOD__);
        pr($a_in);

        static::db()->oQuery("DELETE FROM `" . static::$tableName . "` WHERE id = '$a_in[id]'");

        return $a_in;
    }

}
