<?php
// mysqli_report(MYSQLI_REPORT_ERROR);
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

class x_Mysql extends Mysqli
{
    public $user = null;
    public $host = 'localhost';
    public $passwd = null;
    public $port = '3306';
    public $socket = null;
    public $flags = 0;
    public $name = null;

    public static $a_multitons = [];

    public static $a_cache = [];

    public function __construct($m_param = null)
    {
        $className = get_called_class();

        // 		h(__method__);

        if (is_array($m_param) or is_object($m_param)) {
            foreach ($m_param as $k => $v) {
                $this->$k = $v;
            }
        }

        if (gethostname() == $this->host) {
            $this->host = 'localhost';
        }

        parent::__construct();

        @$this->real_connect(
            $this->host,
            $this->user,
            $this->passwd,
            $this->name,
            $this->port,
            $this->socket,
            $this->flags
        );

        $this->passwd = 'xxxx';

        $this->query("SET NAMES 'UTF8'");

        return true;
    }

    public static function &multiton($m_param = null)
    {
        $className = get_called_class();

        $md5 = md5(implode('|', $m_param));

        if (empty(static::$a_multitons[$md5])) {
            static::$a_multitons[$md5] = new x_Mysql($m_param);
        }

        return static::$a_multitons[$md5];
    }
    public function oQuery($query)
    {
        if ($GLOBALS['DEBUG_SQL'] == true) {
            $a_query = [];

            foreach (explode("\n", $query) as $k => $v) {
                if (!empty($v) and ($v = trim($v)) and !empty($v)) {
                    $a_query[] = "  $v";
                }
            }

            $a_tmp = debug_backtrace(false);

            $a_trace = array_pop($a_tmp);

            pr(
                "---  SQL  ---\n" .
                implode("\n", $a_query) .
                "\n\n  Called from $a_trace[file]:$a_trace[line]\n-------------",
                "r:db={$this->name}"
            );
        }

        if (isset($GLOBALS['PROFILER_SQL']) and $GLOBALS['PROFILER_SQL'] == true) {
            x_Profiler::start(get_called_class() . '::query()');
        }

        try {
            $to_return = $this->query($query);
        } catch (Exception $e) {
            $a_query = [];

            foreach (explode("\n", $query) as $k => $v) {
                if (!empty($v) and ($v = trim($v)) and !empty($v)) {
                    $a_query[] = "  $v";
                }
            }

            $a_tmp = debug_backtrace(false);
            h($this->error);
            pr($a_tmp);
            // 			$a_trace = array_pop($a_tmp);
            //
            // 			$msg = "  Called from $a_trace[file]:$a_trace[line]\n$this->error\n---  SQL  ---\n".implode("\n",$a_query)."\n-------------\n";

            //  			e($msg);
            h();

            // 			exit;
            //   			throw new Exception($msg);
        }

        if (isset($GLOBALS['PROFILER_SQL']) and $GLOBALS['PROFILER_SQL'] == true) {
            x_Profiler::stop(get_called_class() . '::query()', $query);
        }

        return $to_return;
    }

    public function &oQueryFetchArray($query, $use = '')
    {
        if (!empty($use)) {
            $this->oQuery("use $use");
        }

        $o_result = $this->oQuery($query);

        if (extension_loaded('mysqlnd')) {
            if ($a_return = $o_result->fetch_all(MYSQLI_ASSOC)) {
            } else {
                $a_return = [];
            }
        } else {
            while ($fa_result = $o_result->fetch_assoc()) {
                $a_return[] = $fa_result;
            }
        }

        return $a_return;
    }

    public function oQueryFetchArraySingle($query)
    {
        $o_result = $this->oQuery($query);

        return $o_result->fetch_assoc();
    }

    public function oQueryFetchArraySinglePop($query)
    {
        $o_result = $this->oQuery($query);

        $a_res = $o_result->fetch_assoc();

        if (!empty($a_res)) {
            return array_pop($a_res);
        }
    }

    public function oTablePath($tableName)
    {
        return "`$this->name`.`$tableName`";
    }

    public function oInsert($tableName, $data)
    {
        $b_flag = false;

        $dbTablePath = $this->oTablePath($tableName);
        $id = $this->oGetPrimaryKeyName($tableName);

        foreach ($data as $k => $v) {
            if (preg_match('/^_/', $k)) {
                continue;
            }

            if ($k == $id and ($v == '0' or $v == 'null')) {
                continue;
            }

            if ($v !== null) {
                $b_flag = true;

                $v = $this->real_escape_string($v);

                $a_fields[] = "`$k`";

                if (strtolower($v) === 'null') {
                    $a_values[] = 'NULL';
                } else {
                    $a_values[] = "'$v'";
                }
            }
        }

        if (!$b_flag) {
            return false;
        }

        $sql =
            "
			INSERT INTO $dbTablePath
			(" .
            implode(',', $a_fields) .
            ")

			VALUES
			(" .
            implode(',', $a_values) .
            ")
		";

        $this->oQuery($sql);

        return $this->oGetPrimaryKeyValue($tableName, $data);
    }

    public function oInsertIgnore($tableName, $data)
    {
        $b_flag = false;

        $dbTablePath = $this->oTablePath($tableName);
        $id = $this->oGetPrimaryKeyName($tableName);

        foreach ($data as $k => $v) {
            if (preg_match('/^_/', $k)) {
                continue;
            }

            if ($k == $id and ($v == '0' or $v == 'null')) {
                continue;
            }

            if ($v !== null) {
                $b_flag = true;

                $v = $this->real_escape_string($v);

                $a_fields[] = "`$k`";

                if (strtolower($v) === 'null') {
                    $a_values[] = 'NULL';
                } else {
                    $a_values[] = "'$v'";
                }
            }
        }

        if (!$b_flag) {
            return false;
        }

        $sql =
            "
			INSERT IGNORE INTO $dbTablePath
			(" .
            implode(',', $a_fields) .
            ")

			VALUES
			(" .
            implode(',', $a_values) .
            ")
		";

        $sql = preg_replace('%,$%', '', $sql);

        $this->oQuery($sql);

        return $this->oGetPrimaryKeyValue($tableName, $data);
    }

    public function oInsertUpdate($tableName, $data)
    {
        $b_flag = false;

        $dbTablePath = $this->oTablePath($tableName);
        $id = $this->oGetPrimaryKeyName($tableName);

        foreach ($data as $k => $v) {
            if (preg_match('/^_/', $k)) {
                continue;
            }

            if ($k == $id and ($v == '0' or $v == 'null')) {
                continue;
            }

            if ($v !== null) {
                $b_flag = true;

                $v = $this->real_escape_string($v);

                $a_fields[] = "`$k`";
                $a_ONDUPK[] = "`$k` = VALUES (`$k`)";

                if (strtolower($v) === 'null') {
                    $a_values[] = 'NULL';
                } else {
                    $a_values[] = "'$v'";
                }
            }
        }

        if (!$b_flag) {
            return false;
        }

        $sql =
            "
			INSERT $dbTablePath
			(" .
            implode(',', $a_fields) .
            ")

			VALUES
			(" .
            implode(',', $a_values) .
            ")

			ON DUPLICATE KEY UPDATE \n" .
            implode(",\n", $a_ONDUPK);

        $sql = preg_replace('%,$%', '', $sql);

        $this->oQuery($sql);

        return $this->oGetPrimaryKeyValue($tableName, $data);
    }

    public function oMultiInsert($tableName, $a_data)
    {
        $dbTablePath = $this->oTablePath($tableName);
        $id = $this->oGetPrimaryKeyName($tableName);

        foreach ($a_data as $a) {
            foreach ($a as $k => $v) {
                if (preg_match('/^_/', $k)) {
                    continue;
                }

                if ($k == $id and ($v == '0' or $v == 'null')) {
                    continue;
                }

                if ($v !== null) {
                    $a_fields[] = "`$k`";
                }
            }

            break;
        }

        $sql =
            "INSERT INTO $dbTablePath\n(" .
            implode(',', $a_fields) .
            ")\n\nVALUES\n";

        foreach ($a_data as $a) {
            $b_flag = false;

            $a_values = [];

            foreach ($a as $k => $v) {
                if (preg_match('/^_/', $k)) {
                    continue;
                }

                if ($k == $id and ($v == '0' or $v == 'null')) {
                    continue;
                }

                if ($v !== null) {
                    $b_flag = true;

                    $v = $this->real_escape_string($v);

                    if (strtolower($v) === 'null') {
                        $a_values[] = 'NULL';
                    } else {
                        $a_values[] = "'$v'";
                    }
                }
            }

            if ($b_flag == true) {
                $sql .= '(' . implode(',', $a_values) . "),\n";
            }
        }

        if (!$b_flag) {
            return false;
        }

        $sql = preg_replace('%,$%', '', $sql);

        $this->oQuery($sql);

        return $this->affected_rows;
    }

    public function oMultiInsertIgnore($tableName, $a_data)
    {
        $dbTablePath = $this->oTablePath($tableName);
        $id = $this->oGetPrimaryKeyName($tableName);

        foreach ($a_data as $a) {
            foreach ($a as $k => $v) {
                if (preg_match('/^_/', $k)) {
                    continue;
                }

                if ($k == $id and ($v == '0' or $v == 'null')) {
                    continue;
                }

                if ($v !== null) {
                    $a_fields[] = "`$k`";
                }
            }

            break;
        }

        $sql =
            "INSERT IGNORE INTO $dbTablePath\n(" .
            implode(',', $a_fields) .
            ")\n\nVALUES\n";

        foreach ($a_data as $a) {
            $b_flag = false;

            $a_values = [];

            foreach ($a as $k => $v) {
                if (preg_match('/^_/', $k)) {
                    continue;
                }

                if ($k == $id and ($v == '0' or $v == 'null')) {
                    continue;
                }

                if ($v !== null) {
                    $b_flag = true;

                    $v = $this->real_escape_string($v);

                    if (strtolower($v) === 'null') {
                        $a_values[] = 'NULL';
                    } else {
                        $a_values[] = "'$v'";
                    }
                }
            }

            if ($b_flag == true) {
                $sql .= '(' . implode(',', $a_values) . "),\n";
            }
        }

        if (!$b_flag) {
            return false;
        }

        $sql = preg_replace('%,$%', '', $sql);

        $this->oQuery($sql);

        return $this->affected_rows;
    }

    public function oMultiInsertUpdate($tableName, $a_data)
    {
        $dbTablePath = $this->oTablePath($tableName);
        $id = $this->oGetPrimaryKeyName($tableName);

        foreach ($a_data as $a) {
            foreach ($a as $k => $v) {
                if (preg_match('/^_/', $k)) {
                    continue;
                }

                if ($k == $id and ($v == '0' or $v == 'null')) {
                    continue;
                }

                if ($v !== null) {
                    $a_fields[] = "`$k`";
                    $a_ONDUPK[] = "`$k` = VALUES (`$k`)";
                }
            }

            break;
        }

        $sql =
            "INSERT INTO $dbTablePath\n(" .
            implode(',', $a_fields) .
            ") \n\nVALUES\n";

        foreach ($a_data as $a) {
            $b_flag = false;

            $a_values = [];

            foreach ($a as $k => $v) {
                if (preg_match('/^_/', $k)) {
                    continue;
                }

                if ($k == $id and ($v == '0' or $v == 'null')) {
                    continue;
                }

                if ($v !== null) {
                    $b_flag = true;

                    $v = $this->real_escape_string($v);

                    if (strtolower($v) === 'null') {
                        $a_values[] = 'NULL';
                    } else {
                        $a_values[] = "'$v'";
                    }
                }
            }

            if ($b_flag == true) {
                $sql .= '(' . implode(',', $a_values) . "),\n";
            }
        }

        if (!$b_flag) {
            return false;
        }

        $sql = preg_replace('%,$%', '', $sql);

        $sql = "$sql \nON DUPLICATE KEY UPDATE \n" . implode(",\n", $a_ONDUPK);

        $sql = preg_replace('%,$%', '', $sql);

        $this->oQuery($sql);

        return $this->affected_rows;
    }

    public function oGetKeysFromTable($tableName)
    {
        $dbTablePath = $this->oTablePath($tableName);

        $a_indexes = [];

        foreach ($this->oQueryFetchArray("SHOW INDEX FROM $dbTablePath") as $v) {
            if ($v['Key_name'] != 'PRIMARY') {
                $a_indexes[$v['Column_name']] = $v['Column_name'];
            }
        }

        return $a_indexes;
    }

    public function oGetPrimaryKeyFromTable($tableName)
    {
        $dbTablePath = $this->oTablePath($tableName);

        $a_indexes = [];

        foreach ($this->oQueryFetchArray("SHOW INDEX FROM $dbTablePath") as $v) {
            if ($v['Key_name'] == 'PRIMARY') {
                return $v['Column_name'];
            }
        }
    }

    public function oGetPrimaryKeyValue($tableName, $data)
    {
        if ($this->insert_id > 0) {
            return (int) $this->insert_id;
        }

        $primaryKeyName = $this->oGetPrimaryKeyName($tableName);

        $a_where = [];

        if (is_object($data)) {
            if (!empty($data->$primaryKeyName)) {
                return $data->$primaryKeyName;
            }

            foreach ($this->oGetKeysFromTable($tableName) as $key) {
                if (!empty($data->$key)) {
                    $a_where[] = "`$key` = '" . $data->$key . "'";
                }
            }
        } elseif (is_array($data)) {
            if (!empty($data[$primaryKeyName])) {
                return $data[$primaryKeyName];
            }

            foreach ($this->oGetKeysFromTable($tableName) as $key) {
                if (!empty($data[$key])) {
                    $a_where[] = "`$key` = '" . $data[$key] . "'";
                }
            }
        }

        if (count($a_where) > 0) {
            $sql =
                "
				SELECT $primaryKeyName
				FROM " .
                $this->oTablePath($tableName) .
                "
				WHERE " .
                implode(' AND ', $a_where) .
                " LIMIT 1
			";

            $result = $this->oQueryFetchArraySingle($sql);

            if (is_array($result)) {
                return (int) array_pop($result);
            }
        }

        return null;
    }

    public function oGetPrimaryKeyName($tableName)
    {
        if (!isset(static::$a_cache[$tableName])) {
            $array = $this->oQueryFetchArraySingle(
                "SHOW KEYS FROM `$tableName` WHERE Key_name = 'PRIMARY'"
            );

            static::$a_cache[$tableName] = $array['Column_name'];
        }

        return static::$a_cache[$tableName];
    }

    public function oRead($tableName, $id, $fields = '*')
    {
        return $this->oQueryFetchSingleObject(
            "SELECT $fields FROM $tableName WHERE id = '$id' LIMIT 1",
            "m_$tableName"
        );
    }

    public function aRead($tableName, $id, $fields = '*')
    {
        return $this->oQueryFetchArraySingle(
            "SELECT $fields FROM $tableName WHERE id = '$id' LIMIT 1"
        );
    }

    public function oGetFieldsFromTable($tableName)
    {
        $a1s = $this->oQueryFetchArray("
			SHOW COLUMNS FROM `$tableName`
		");

        $a2s = [];

        foreach ($a1s as $a1) {
            $a2s[] = $a1['Field'];
        }

        return $a2s;
    }

    public function oGetFieldsFromTableExcept($tableName, $a_except = [])
    {
        $a1s = $this->oQueryFetchArray("
			SHOW COLUMNS FROM `$tableName`
		");

        $a2s = [];

        foreach ($a1s as $a1) {
            if (!in_array($a1['Field'], $a_except)) {
                $a2s[] = $a1['Field'];
            }
        }

        return $a2s;
    }

    public function oFieldIsInTable($tableName, $field)
    {
        $a_fields = $this->oGetFieldsFromTable($tableName);

        return in_array($field, $a_fields);
    }

    public function oTableIsInDatabase($tableName)
    {
        $a1s = $this->oQueryFetchArray("
			SHOW TABLES
		");

        $a2s = [];

        foreach ($a1s as $a1) {
            $a2s[] = array_pop($a1);
        }

        return in_array($tableName, $a2s);
    }
}