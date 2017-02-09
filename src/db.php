<?php

namespace PDO;
/**
 * Class PDO
 * @package App
 */
class PDO
{

    /**
     * @var
     */
    protected $db;

   const DB_SERVER = "localhost";
   const DB_USER = "root";
   const DB_PASSWORD = "root";

    /**
     *
     */
    const DB = "YOUR_DATABASE";

    /**
     * PDO constructor.
     * @param array $attributes
     */
    public function __construct()
    {
        $this->dbConnect();
    }

    /** ------------------------------------------------------------------------------------ */
    /** Base Class Functions */
    /** ------------------------------------------------------------------------------------ */

    protected function dbConnect()
    {
        try {

            if (!$this->db) {
                $this->db = new \PDO('mysql:host=' . self::DB_SERVER . ';dbname=' . self::DB, self::DB_USER, self::DB_PASSWORD);
                $this->db->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            }
        } catch (\PDOException $e) {
            echo 'ERROR: ' . $e->getMessage();
            die();
        }
    }


    public function groupInsert($table, $array)
    {
        foreach ($array as $row) {
            $this->coreInsert($table, $row);
        }
    }

    public function groupUpdate($table, $array, $filter, $rule)
    {
        foreach ($array as $row) {
            $filter_query = '';
            if (is_array($filter)) {
                foreach ($filter as $index => $item) {
                    $filter_query .= $item . '=' . $row[$item] . ' ' . $rule . ' ';
                }
            }
            $filter_rule = rtrim($filter_query, ' ' . $rule . ' ');

            $this->coreUpdate($table, $row, $filter_rule, false, true);
        }
    }

    /**
     * Directly insert into the table.
     *
     * @param $table
     * @param $inputs
     * @return bool
     */
    public function coreInsert($table, $inputs)
    {
        $data = '"' . implode('","', array_values($inputs)) . '"';
        $inputs = implode(',', array_keys($inputs));

        $record = $this->db->prepare("INSERT INTO {$table}({$inputs}) VALUES({$data})");
        $record->execute();
        return $this->db->lastInsertId();
    }


    /**
     *
     * To Update the table data.
     *
     * @param $table
     * @param $inputs
     * @param $filter
     * @param $value
     */
    public function coreUpdate($table, $inputs, $filter, $value, $direct_filter = false)
    {
        $query = '';
        foreach ($inputs as $index => $input) {
            if (empty($input) || is_null($input)) $input = '0';

            $query .= $index . '="' . $input . '",';
        }
        $query = rtrim($query, ',');
        if ($direct_filter) {
            $sql = "UPDATE {$table} SET {$query} WHERE {$filter}";
        } else {
            $sql = "UPDATE {$table} SET {$query} WHERE {$filter}={$value}";
        }
        $a = $sql;
        $record = $this->db->prepare($sql);
        $record->execute();
    }

    /**
     * @param $table
     * @param string $fields
     * @param bool $where
     * @param string $filter
     * @return array
     */
    public function coreSelect($table, $fields = '*', $where = false, $filter = '')
    {

        // General Query Structure.
        $query = "SELECT {$fields} FROM {$table}";

        // If, Where condition is applied.
        if ($where) {
            if (is_array($where)) {
                $where_array = '';
                foreach ($where as $index => $condition) {

                    if (!isset($condition['rel'])) $condition['rel'] = '';

                    $where_array .= " {$condition['field']} {$condition['condition']} '{$condition['value']}' {$condition['rel']}";
                    $rel = $condition['rel'];
                }
                $where = " WHERE " . rtrim($where_array, $rel);
            } else {
                $where = "WHERE {$where['field']} {$where['condition']} {$where['value']}";
            }
        }

        $query = $query . $where . ' ' . $filter;

        $records = $this->db->prepare($query);
        $records->execute();
        if ($records->rowCount() > 0)
            $row = $records->fetchAll(\PDO::FETCH_ASSOC);
        else
            $row = array();

        return $row;
    }
}