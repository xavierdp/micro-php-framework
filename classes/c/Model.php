<?php
class c_Model
{
    /**
     * default db
     */
    public static $db = "db0";

    /**
     * default table name
     */
    static $tableName = null;

    public static function db()
    {
        $db = static::$db;

        return $db();
    }


    public static function insert($a_in)
    {
        h(__METHOD__);
        pr($a_in);

        /**
         * remove id to force auto increment
         */
        unset($a_in["id"]);


        $a_in["id"] = static::db()->oInsert(static::$tableName, $a_in);

        return $a_in;
    }

    public static function update($a_in)
    {
        h(__METHOD__);
        pr($a_in);

        /**
         * use the ON DUPLICATE KEY INERT UPDATE syntax behind 
         * so use keys to force update based on id or others keys
         * 
         * if the tuple already exists, it will be updated
         * if it doesn't exist, it will be inserted
         * 
         * the current id is returned
         */

        $a_in["id"] = static::db()->oInsertUpdate(static::$tableName, $a_in);

        return $a_in;
    }

    public static function delete($a_in)
    {
        h(__METHOD__);
        pr($a_in);

        /**
         * delete by id
         */

        static::db()->oQuery("DELETE FROM " . static::$tableName . " WHERE id= $a_in[id]");

        return ["id" => $a_in["id"]];
    }

}