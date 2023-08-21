<?php
/**
 * How to use examples. All values are auto escaped with sql_escape_string.
 * If no select field is specified, it select '*' as default.
 * The default MySQL connection used is 'db0'.
 * 
 * Build Query :
 * DB::table('tableName')->connection('dbAuth')->get();
 * DB::table('tableName')->get();
 * DB::table('tableName')->groupBy('category')->get();
 * DB::table('tableName')->paginate();
 * DB::table('tableName')->orderBy('id')->get();
 * DB::table('tableName')->orderBy('id', 'DESC')->get();
 * DB::table('tableName')->select("id", "count", "views")->get();
 * DB::table('tableName', 'alias1')->from("anotherTable", "alias2")->select("alias1.id", "alias2.count", "alias1.views")->get();
 * DB::table('tableName')->where(['active', '1'])->get();
 * DB::table('tableName')->where([['active', '1']])->orWhere([['active', '2']])->get();
 * DB::table('tableName')->where([['active', '=', '1'], ['views', '>', '0']])->first();
 * DB::table('tableName')->where([['active', '=', '1'], ['views', '>', '0']])->skip(5)->take(10)->get();
 * DB::table('tableName')->where(['active', '0'])->update(['active' => '1"]);
 * DB::table('tableName')->insert(['first_name' => 'john", 'last_name' => 'doe']);
 * DB::table('tableName')->pop("views");
 * DB::table('tableName')->limit(5)->get();
 * DB::table('tableName')->limit(5, 10)->get();
 * DB::table('tableName')->where(["expired", "1"])->delete();
 * DB::table('tableName')->where(["expired", "1"])->count();
 * DB::table('articles', "a")->where(["expired", "1"])->join("users", "a.user_id", "users.id", "LEFT")->get();
 * 
 * Or simply raw query :
 * DB::query("SELECT * FROM matable");
 */
class DB extends x_QueryBuilder
{
    /**
     * Set to true to return data as model objects.
     *
     * @var boolean
     */
    public static $as_object = false;

    /**
     * A Query Builder to build raw query with methods as Object
     *
     * @param string $table
     * @param string|null $alias
     * @return x_QueryBuilder Instantiate a QueryBuilder from the specified $table with optionnal $alias
     */
    public static function table(string $table, string $alias = null): x_QueryBuilder
    {
        $query = new x_QueryBuilder(static::$as_object);
        return $query->from($table, $alias);
    }

    /**
     * Do a raw SQL query
     *
     * @param string $sql the raw sql query
     * @param string $connection The x_Mysql connection to use
     * @return any the query result
     */
    public static function query(string $sql, string $connection = "db0")
    {
        $query = new x_QueryBuilder();
        $query->connection($connection)->getConnection()->oQuery($sql);
    }

    public static function now()
    {
        return date("Y-m-d H:i:s");
    }
}


class x_QueryBuilder
{
    /**
     * @var string
     */
    private $connection = "db0";

    /**
     * @var array<string>
     */
    private $fields = [];

    /**
     * @var array<string>
     */
    private $conditions = [];

    /**
     * @var array<array>string>>
     */
    private $orConditions = [];

    /**
     * @var array<string>
     */
    private $joins = [];

    /**
     * @var array<string>
     */
    private $from = [];

    /**
     * @var int
     */
    private $limit = null;

    /**
     * @var int
     */
    private $skip = null;

    /**
     * @var int
     */
    private $take = null;

    /**
     * @var string
     */
    private $orderBy = null;

    /**
     * @var string
     */
    private $groupBy = null;

    /**
     * @var boolean
     */
    private $itsCount = false;

    /**
     * @var string
     */
    private $sumField = null;

    /**
     * @var string
     */
    public $rawSql = null;

    /**
     * @var boolean
     */
    private $as_object;

    /**
     * @var stdClass
     */
    public $datas = null;


    /**
     * Instantiate a new x_QueryBuilder object
     *
     * @param boolean $as_object If true, all data are returned as object. If false, all data are returned as array
     */
    public function __construct($as_object = false) {
        $this->as_object = $as_object;
    }



    /**
     * Specify the MySQL connection to use.
     *
     * @param string $connection
     * @return self
     */
    public function connection(string $connection): self
    {
        $this->connection = $connection;
        return $this;
    }

    /**
     * Get the connection object
     *
     * @return x_Mysql connection object
     */
    protected function getConnection()
    {
        h("x_QueryBuilder :: Raw SQL");
        pr($this->rawSql);

        return call_user_func($this->connection);
    }

    /**
     * Do a select and return result in array
     *
     * @return array|null|self Return self if as_object is true, else return array or null
     */
    public function get()
    {

        $select = "SELECT ";
        if ($this->itsCount)
            $select = "SELECT COUNT(*) AS count ";

        $this->rawSql = $select . $this->buildFields()
            . ' FROM ' . $this->buildFroms()
            . $this->buildJoins()
            . $this->buildWhere();

        if ($this->orderBy !== null)
            $this->rawSql .= $this->orderBy;

        if ($this->groupBy !== null)
            $this->rawSql .= $this->groupBy;

        if ($this->limit !== null)
            $this->rawSql .= $this->buildLimits();

        if ($this->limit === 1) {
            $data = $this->getConnection()->oQueryFetchArraySingle($this->rawSql);
            return $this->result($data);
        }

        $data = $this->getConnection()->oQueryFetchArray($this->rawSql);
        return $this->result($data);
    }

    /**
     * Return result as Object or Array depending on as_object value
     *
     * @param any $data
     * @return self
     */
    private function result($data) {
        if(!$this->as_object)
            return $data;

        // convert array as object
        $this->datas = new stdClass();
        foreach ($data as $k => $row) {
            $this->datas->$k = $row;
        }
        
        return $this;
    }

    // return property value
    public function __get($name)
    {
        if(isset($this->datas->{$name}))
            return $this->datas->{$name};

        return null;
    }

    /**
     * Do a count of the builded query
     *
     * @return int the count number
     */
    public function count(): int
    {
        $this->itsCount = true;
        $result = intval($this->pop('count'));

        $this->itsCount = false;
        return $result;
    }

    /**
     * Do a select and only return one result, the first found by the query
     *
     * @return array|null
     */
    public function first()
    {
        $this->limit = 1;
        $result = $this->get();

        $this->limit = null;
        return $result;
    }

    /**
     * Add a SUM on a specific field
     *
     * @param string $field
     * @return float
     */
    public function sum(string $field): float
    {
        $this->sumField = $field;
        return floatval($this->pop($field));
    }

    /**
     * Return paginated result of the query
     *
     * @param integer $itemsPerPage Nb of items per page
     * @param string $pageIndex The name of the $_GET index to specify current page
     * @return array ['results', 'page', 'perPage', 'totalPages', 'totalItems', 'prevPage', 'nextPage']
     */
    public function paginate(int $itemsPerPage = 10, string $pageIndex = "page"): array
    {
        $page = (isset($_GET[$pageIndex]) ? (intval($_GET[$pageIndex])) : 1);
        if ($page < 1) $page = 1;

        $results = [];
        $nbItems = $this->count();

        $pages = ceil($nbItems / $itemsPerPage);
        $skip = ($page * $itemsPerPage) - $itemsPerPage;

        $results = $this->limit($skip, $itemsPerPage)->get();

        $return = [
            "results"      => $results,
            "page"         => $page,
            "perPage"      => $itemsPerPage,
            "totalPages"   => $pages,
            "totalItems"   => $nbItems,
            "prevPage"     => ($page - 1 > 0) ? ("?{$pageIndex}=" . ($page - 1)) : "#!",
            "nextPage"     => ($page < $pages) ? ("?{$pageIndex}=" . ($page + 1)) : "#!"
        ];

        return $this->result($return);
    }

    /**
     * Do a select and only return the specified field from the first row result
     *
     * @return any the raw value
     */
    public function pop(string $field)
    {
        if (!$this->itsCount)
            $this->select($field);

        $result = $this->first();

        if (isset($result[$field]))
            return $result[$field];

        return null;
    }

    /**
     * Do an insert on the specified table
     *
     * @param array $keysValues
     * @return int the inserted id
     */
    public function insert(array $keysValues)
    {
        if (count($keysValues) === 0)
            throw new Exception("No key/value specified for the insert");

        $sets = "";
        $count = count($keysValues);
        $i = 0;
        foreach ($keysValues as $k => $value) {
            $value = sql_escape_string($value);
            $sets .= $k . "='" . $value . "'";

            if ($i < $count - 1)
                $sets .= ",";
            $i++;
        }

        $this->rawSql = 'INSERT INTO ' . $this->buildFroms()
            . ' SET ' . $sets;

        $req = $this->getConnection()->oQuery($this->rawSql);

        // Return last insert id
        return $this->getConnection()->insert_id;
    }

    /**
     * Do an update on the specified table
     *
     * @param array $keysValues
     * @return any the query result
     */
    public function update(array $keysValues)
    {
        h(__METHOD__);
        if (count($keysValues) === 0)
            throw new Exception("No key/value specified for the update");

        $sets = "";
        $count = count($keysValues);
        $i = 0;
        foreach ($keysValues as $k => $value) {
            if($value == null || $value == strtolower("null")) {
                $value = "NULL";
                $sets .= $k . "=" . $value . "";
            }else{
                $value = sql_escape_string($value);
                $sets .= $k . "='" . $value . "'";
            }
            

            if ($i < $count - 1)
                $sets .= ", ";
            $i++;
        }

        $this->rawSql = 'UPDATE ' . $this->buildFroms()
            . ' SET ' . $sets
            . $this->buildWhere();

        return $this->getConnection()->oQuery($this->rawSql);
    }

    /**
     * Do a delete on the specified table
     *
     * @return any the query result
     */
    public function delete()
    {
        $this->rawSql = 'DELETE FROM ' . $this->buildFroms()
            . $this->buildJoins()
            . $this->buildWhere();

        return $this->getConnection()->oQuery($this->rawSql);
    }

    /**
     * Select some fields
     *
     * @param string ...$select
     * @return self
     */
    public function select(string ...$select): self
    {
        $this->fields = array_merge($this->fields, $select);
        return $this;
    }

    /**
     * Specify where AND conditions
     *
     * @param array $where
     * @return self
     */
    public function where(array $where): self
    {
        $count = count($where);
        if (isset($where[0]) && !is_array($where[0])) {
            if ($count === 2 || $count === 3)
                $where = [$where];
            else throw new Exception("Incorrect index count. You need to specify key/value or key/comparator/value in the array.");
        }

        foreach ($where as $arg) {
            $this->conditions[] = $arg;
        }

        return $this;
    }

    /**
     * Specify where OR conditions
     *
     * @param array $where
     * @return self
     */
    public function orWhere(array $where): self
    {
        $count = count($where);
        if (isset($where[0]) && !is_array($where[0])) {
            if ($count === 2 || $count === 3)
                $where = [$where];
            else throw new Exception("Incorrect index count. You need to specify key/value or key/comparator/value in the array.");
        }

        $this->orConditions[] = $where; // Using sub-array to separate each call by another OR (unlike the where method)
        return $this;
    }

    /**
     * Specify a table with an optionnal aliases
     *
     * @param string $table
     * @param string|null $alias
     * @return self
     */
    public function from(string $table, ?string $alias = null): self
    {
        if ($alias === null) {
            $this->from[] = $table;
        } else {
            $this->from[] = "${table} AS ${alias}";
        }

        return $this;
    }


    /**
     * Create join
     *
     * @param string $fromTable the table to join (ex: users). Can use ex "users AS u" to use 'u' as alias.
     * @param string $fromField the table field from to use join. (Ex: u.id)
     * @param string $toField the field to join the $fromField (ex: article.user_id)
     * @param string $joinType the join type (Ex: LEFT|INNER|RIGHT)
     * @return self
     */
    public function join($fromTable, $fromField, $toField, $joinType = "LEFT"): self
    {
        $this->joins[] = [$fromTable, $fromField, $toField, $joinType];
        return $this;
    }

    /**
     * Specify a limit on the query
     *
     * @param integer $limit
     * @return self
     */
    public function limit(int $limit, int $limit2 = null): self
    {
        if ($limit2 === null)
            $this->limit = "" . $limit . "";
        else
            $this->limit = $limit . ", " . $limit2;

        return $this;
    }

    /**
     * Skip N rows
     *
     * @param integer $nb
     * @return self
     */
    public function skip(int $nb): self
    {
        $this->skip = $nb;
        return $this;
    }

    /**
     * Take N rows
     *
     * @param integer $nb
     * @return self
     */
    public function take(int $nb): self
    {
        $this->take = $nb;
        return $this;
    }

    /**
     * Specify a $field order by $type
     *
     * @param string $field
     * @param string $type
     * @return self
     */
    public function orderBy(string $field, string $type = "ASC"): self
    {
        $this->orderBy = " ORDER BY {$field} {$type} ";
        return $this;
    }

    /**
     * Group result by $field
     *
     * @param string $field
     * @return self
     */
    public function groupBy(string $field): self
    {
        $this->groupBy = " GROUP BY {$field} ";
        return $this;
    }




    /**
     * Build query
     */
    private function buildJoins(): string
    {
        $joinStr = "";
        foreach ($this->joins as $join) {
            $joinStr .= " " . $join[3] . " JOIN " . $join[0] . " ON " . $join[1] . "=" . $join[2];
        }

        return $joinStr;
    }

    private function buildLimits(): string
    {
        if ($this->skip !== null && $this->take !== null) {
            $this->limit($this->skip, $this->take);
        } else if ($this->skip !== null) {
            $this->limit($this->skip, 999999999);
        } else if ($this->take !== null) {
            $this->limit($this->take);
        }

        if ($this->limit !== null)
            return " LIMIT " . $this->limit;

        return "";
    }

    private function buildFroms(): string
    {
        if (count($this->from) === 0)
            throw new Exception("No table specified for the query");

        return implode(', ', $this->from);
    }

    private function buildFields(): string
    {
        if ($this->itsCount)
            return "";

        if ($this->sumField != null) {
            $sum = " SUM({$this->sumField}) AS {$this->sumField} ";
            $this->fields[] = $sum;
        }

        if (count($this->fields) === 0)
            $this->fields[] = "*";

        $implode = implode(', ', $this->fields);

        return $implode;
    }

    private function buildWhere(): string
    {
        $where = "";
        $count = count($this->conditions);
        if ($count > 0) {
            $where .= "(";
            foreach ($this->conditions as $k => $w) {
                if (is_array($w)) {

                    // If 3 args, operator is specified
                    if (count($w) === 3) {
                        $value = "''";

                        // If IN operator, check is array
                        if(strtolower($w[1]) === "in" && is_array($w[2])) {
                            $value = "(";
                            foreach($w[2] as $in_k => $in_v) {
                                $value .= "'".sql_escape_string($in_v)."'";

                                if($in_k < count($w[2])-1)
                                    $value .= ",";
                            }
                            $value .= ")";
                        }elseif(str_starts_with(strtolower($w[1]), "raw")) {
                            // If want to send RAW value
                            $w[1] = str_replace("raw", "", strtolower($w[1]));
                            if(empty($w[1]))
                                $w[1] = "=";
                            $value = $w[2];
                        }else{
                            if(strtolower($w[1]) === "in" && !is_array($w[2]))
                                throw new Exception("You need to pass an array for the IN operator");

                            $value = "'".sql_escape_string($w[2])."'";
                        }

                        $where .= " " . $w[0] . " " . $w[1] . " " . $value;
                    } else if (count($w) == 2) {
                        // For 2 args, user passed key/value without operator. Using "="
                        $value = sql_escape_string($w[1]);
                        $where .= " " . $w[0] . "='" . $value . "'";
                    } else {
                        throw new Exception("You need to specify key/value or key/comparator/value in a array");
                    }

                    if ($k < $count - 1)
                        $where .= " AND ";
                } else {
                    throw new Exception("You need to specify an array with key/value or key/comparator/value");
                }
            }
            $where .= ") ";
        }

        // OR are handled differently, using sub_array to use one OR per time calling orWhere
        $count = count($this->orConditions);
        if ($count > 0) {

            foreach ($this->orConditions as $k => $w) {

                if (!is_array($w)) {
                    throw new Exception("You need to specify an array with key/value or key/comparator/value");
                }

                if (strlen($where) > 0)
                    $where .= " OR (";
                else
                    $where .= "(";

                $count = count($w);
                foreach ($w as $kk => $vv) {
                    if (!is_array($vv)) {
                        throw new Exception("You need to specify an array with key/value or key/comparator/value");
                    }

                    // If 3 args, operator is specified
                    if (count($vv) === 3) {
                        $value = "''";

                        // If IN operator, check is array
                        if(strtolower($vv[1]) === "in" && is_array($vv[2])) {
                            $value = "(";
                            foreach($vv[2] as $in_k => $in_v) {
                                $value .= "'".sql_escape_string($in_v)."'";
                                
                                if($in_k < count($vv[2])-1)
                                $value .= ",";
                            }
                            $value .= ")";
                        }elseif(str_starts_with(strtolower($vv[1]), "raw")) {
                            $vv[1] = str_replace("raw", "", strtolower($vv[1]));
                            if(empty($vv[1]))
                                $vv[1] = "=";
                            $value = $vv[2];
                        }else{
                            if(strtolower($vv[1]) === "in" && !is_array($vv[2]))
                                throw new Exception("You need to pass an array for the IN operator");
                            
                            $value = "'".sql_escape_string($vv[2])."'";
                        }

                        $where .= " " . $vv[0] . " " . $vv[1] . " " . $value;
                    } else if (count($vv) == 2) {
                        // For 2 args, user passed key/value without operator. Using "="
                        $value = sql_escape_string($vv[1]);
                        $where .= " " . $vv[0] . "='" . $value . "'";
                    } else {
                        throw new Exception("You need to specify key/value or key/comparator/value in a array");
                    }

                    if ($kk < $count - 1)
                        $where .= " AND ";
                }
                
                $where .= ") ";
            }

        }

        return (strlen($where) > 0 ? " WHERE " . $where : "");
    }
}
