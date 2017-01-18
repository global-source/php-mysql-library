<?php

/**
 * Class DB is a simple and lightly coded instance
 * for many different Data access through MySQL
 * Database.
 */
class DB
{
    /**
     * @var
     */
    public $pdo;

    /**
     * functions constructor.
     */
    public function __construct()
    {
        global $pdo;
        $pdo = new PDO('mysql:host=' . HOST . ';dbname=' . DB, USER, PWD);
    }

    /**
     * @param $pluck
     * @param $arr
     * @return array
     */
    public function array_pluck($pluck, $arr)
    {
        return array_map(function ($item) use ($pluck) {
            return is_object($item) ? $item->$pluck : $item[$pluck];
        }, $arr);
    }

    /**
     * @param $pluck
     * @param $arr
     * @return array
     */
    public function arr_array_pluck($pluck, $arr)
    {
        return array_map(function ($item) use ($pluck) {
            return $item[$pluck];
        }, $arr);
    }

    /**
     * @param $table
     * @param $arr
     * @return bool
     */
    public function is_exist($table, $arr)
    {
        global $pdo;
        $query1 = sprintf("SELECT id FROM %s", $table);
        if (is_array($arr)) {
            $where = $this->_where($arr);
            extract($where);
            $query = $query1 . ' WHERE ' . $query2;
            $stmt = $pdo->prepare($query);
            $stmt->execute($data);
        } else {
            $stmt = $pdo->prepare($query1);
            $stmt->execute();
        }
        return ($stmt->rowCount() > 0) ? $stmt->rowCount() : false;
    }

    // WHERE
    /**
     * @param $arr
     * @return array|bool
     */
    public function _where($arr)
    {
        $result = array();
        $y = 1;
        $query = '';
        foreach ($arr as $key => $value) {
            $data[':' . $key] = $value;
            $query .= ($y == count($arr)) ? $key . ' = :' . $key : $key . ' = :' . $key . ' AND ';
            $y++;
        }
        $result['data'] = $data;
        $result['query2'] = $query;
        return ($result) ? $result : false;
    }

    /**
     * @param $table
     * @param string $orderby
     * @return bool
     */
    public function getData($table, $orderby = "DESC")
    {
        $order = array('id' => $orderby);
        return $this->FetchAll_Records(compact('table', 'order'));
    }

    /**
     * @param $query
     * @param null $where
     * @return bool
     */
    public function GetRecords($query, $where = NULL)
    {
        global $pdo;
        $stmt = $pdo->prepare($query);
        $stmt->execute($where);
        return ($stmt->rowCount() > 0) ? $stmt->fetchAll(PDO::FETCH_OBJ) : false;
    }

    /**
     * @param $Data
     * @return bool
     */
    public function FetchAll_Records($Data)
    {
        # $fields Not Required
        # $order Not Required
        # $where Not Required
        # $table Required
        extract($Data);
        global $pdo;
        $FieldList = isset($fields) ? implode(', ', $fields) : '*';
        if (isset($order)) {
            $key = key($order);
            $by = $order[$key];
            $query3 = " ORDER BY {$key} {$by}";
        } else {
            $query3 = "";
        }
        $query1 = sprintf("SELECT %s FROM %s", $FieldList, $table);
        if (isset($where) && is_array($where)) {
            $where = $this->_where($where);
        } else {
            $where = false;
        }
        if ($where) {
            extract($where);
            $query = $query1 . ' WHERE ' . $query2 . $query3;
            $stmt = $pdo->prepare($query);
            $stmt->execute($data);
        } else {
            $query = $query1 . $query3;
            $stmt = $pdo->prepare($query);
            $stmt->execute();
        }
        return ($stmt->rowCount() > 0) ? $stmt->fetchAll(PDO::FETCH_OBJ) : false;
    }

    /**
     * @param $query
     * @param $data
     * @return bool
     */
    public function FetchArray($query, $data)
    {
        global $pdo;
        $stmt = $pdo->prepare($query);
        $stmt->execute($data);
        return ($stmt->rowCount() > 0) ? $stmt->fetchAll(PDO::FETCH_ASSOC) : false;
    }

    /**
     * @param $table
     * @param $limit
     * @return bool
     */
    public function getDataWithLimit($table, $limit)
    {
        global $pdo;
        $stmt = $pdo->prepare("SELECT * FROM {$table} ORDER BY id DESC limit {$limit}");
        $stmt->execute();
        return ($stmt->rowCount() > 0) ? $stmt->fetchAll(PDO::FETCH_OBJ) : false;
    }

    /**
     * @param $table
     * @param $field
     * @param $data
     * @return bool
     */
    public function getDataWithCondition($table, $field, $data)
    {
        $where = array($field => $data);
        return $this->FetchAll_Records(compact('table', 'where'));
    }

    /**
     * @param $table
     * @param $field
     * @param $where
     * @return bool
     */
    public function getDataWithCondition1($table, $field, $where)
    {
        return $this->getDataWithCondition($table, $field, $where);
    }

    /**
     * @param $query
     * @param $data
     * @return bool
     */
    public function GetSelectiveRecords($query, $data)
    {
        global $pdo;
        $stmt = $pdo->prepare($query);
        $stmt->execute(array(strtolower($data)));
        return ($stmt->rowCount() > 0) ? $stmt->fetchAll(PDO::FETCH_OBJ) : false;
    }

    // FETCH RECORD
    /**
     * @param $table
     * @param $arr
     * @param string $FieldList
     * @param null $order
     * @return bool
     */
    public function FetchRecord($table, $arr, $FieldList = '*', $order = NULL)
    {
        global $pdo;
        if (is_array($FieldList)) {
            $FieldList = implode(',', $FieldList);
        }
        $query1 = sprintf("SELECT %s FROM %s", $FieldList, $table);
        $where = is_array($arr) ? $this->_where($arr) : false;
        if ($where) {
            if (is_null($order)) {
                $query3 = "";
            } else {
                $key = key($order);
                $by = $order[$key];
                $query3 = " ORDER BY {$key} {$by}";
            }
            extract($where);
            $query = $query1 . ' WHERE ' . $query2 . $query3;
            $stmt = $pdo->prepare($query);
            $stmt->execute($data);
        } else {
            $stmt = $pdo->prepare($query1);
            $stmt->execute();
        }
        return ($stmt->rowCount() > 0) ? $stmt->fetch(PDO::FETCH_OBJ) : false;
    }

    /**
     * @param $query
     * @param null $data
     * @return bool
     */
    public function GetRecord($query, $data = NULL)
    {
        global $pdo;
        $stmt = $pdo->prepare($query);
        ($data !== NULL) ? $stmt->execute($data) : $stmt->execute();
        return ($stmt->rowCount() > 0) ? $stmt->fetch(PDO::FETCH_OBJ) : false;
    }

    /**
     * @param $table
     * @param $data
     * @param string $field
     * @return bool
     */
    public function findField($table, $data, $field = "id")
    {
        $arr = array($field => $data);
        return $this->FetchRecord($table, $arr);
    }

    // FETCH ALL RECORD
    /**
     * @param $query
     * @param null $data
     * @return bool
     */
    public function Fetch_Datas($query, $data = NULL)
    {
        global $pdo;
        $stmt = $pdo->prepare($query);
        (is_null($data)) ? $stmt->execute() : $stmt->execute($data);
        return ($stmt->rowCount() > 0) ? $stmt->fetchAll(PDO::FETCH_OBJ) : false;
    }

    // INSERT RECORD
    /**
     * @param $table
     * @param $data
     * @return mixed
     */
    public function InsertRecords($table, $data)
    {
        global $pdo;
        $query = 'YOUR QUERY FOR INSERTION';
        $stmt = $pdo->prepare($query);
        return $stmt->execute($data);
    }

    /**
     * @param $table
     * @param $fields
     * @param $data
     * @return mixed
     */
    public function InsertRecord($table, $fields, $data)
    {
        global $pdo;
        $query = 'YOUR QUERY FOR INSERTION';
        $stmt = $pdo->prepare($query);
        return $stmt->execute($data);
    }

    /**
     * @param $table
     * @param $in_array
     * @param $condition
     * @param string $fields
     * @return bool
     */
    public function IN_Cause($table, $in_array, $condition, $fields = '*')
    {
        global $pdo;
        $in = str_repeat('?,', count($in_array) - 1) . '?';
        $sql = "SELECT $fields FROM $table WHERE $condition IN ($in)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($in_array);
        return ($stmt->rowCount() > 0) ? $stmt->fetchAll(PDO::FETCH_OBJ) : false;
    }

    /**
     * @param $table
     * @param $data
     * @return mixed
     */
    public function UpdateRecord($table, $data)
    {
        global $pdo;
        $query = 'YOUR QUERY FOR INSERTION';
        $stmt = $pdo->prepare($query);
        return $stmt->execute($data);
    }

    /**
     * @param $table
     * @param $data
     * @return bool
     */
    public function delete_record($table, $data)
    {
        global $pdo;
        $stmt = $pdo->prepare("DELETE FROM $table WHERE id = ?");
        $stmt->execute(array($data));
        return ($stmt->rowCount() > 0) ? true : false;
    }

    // INSERT & UPDATE RECORD
    /**
     * @param $query
     * @param $data
     * @return mixed
     */
    public function UpdateRecords($query, $data)
    {
        global $pdo;
        $stmt = $pdo->prepare($query);
        return $stmt->execute($data);
    }

    /**
     * @param $query
     * @param $data
     * @return mixed
     */
    public function QueryProcess($query, $data)
    {
        global $pdo;
        $stmt = $pdo->prepare($query);
        return $stmt->execute($data);
    }

    // FIND LAST INSERT ID
    /**
     * @param $query
     * @param $data
     * @return mixed
     */
    public function Insert_ReturnID($query, $data)
    {
        global $pdo;
        $stmt = $pdo->prepare($query);
        $stmt->execute($data);
        return $pdo->lastInsertId();
    }

    // QUERY RECORD
    /**
     * @param $query
     * @return mixed
     */
    public function QueryMethod($query)
    {
        global $pdo;
        return $pdo->query($query);
    }

    // CHECK RECORD
    /**
     * @param $query
     * @param $data
     * @return bool
     */
    public function CheckAvailable($query, $data)
    {
        global $pdo;
        $stmt = $pdo->prepare($query);
        $stmt->execute($data);
        return ($stmt->rowCount() > 0) ? $stmt->rowCount() : false;
    }

    /**
     * @param $table
     * @param $field
     * @param $Condition
     * @return bool
     */
    public function checkAvailability($table, $field, $Condition)
    {
        global $pdo;
        $stmt = $pdo->prepare("SELECT * FROM {$table} WHERE {$field} = ? LIMIT 1");
        $stmt->execute(array($Condition));
        return ($stmt->rowCount() > 0) ? $stmt->rowCount() : false;
    }

    /**
     * @param $table
     * @param $field
     * @param $Condition
     * @return bool
     */
    public function checkAvailability1($table, $field, $Condition)
    {
        global $pdo;
        $query = 'YOUR QUERY FOR INSERTION';
        $stmt = $pdo->prepare($query);
        $stmt->execute($Condition);
        return ($stmt->rowCount() > 0) ? $stmt->rowCount() : false;
    }

    /**
     * @param $array
     * @param $key
     * @param $val
     * @return bool
     */
    public function find_key_value($array, $key, $val)
    {
        foreach ($array as $item) {
            if (is_array($item)) {
                $this->find_key_value($item, $key, $val);
            }
            if (isset($item[$key]) && ($item[$key] == $val)) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param $Data
     * @return bool
     */
    public function ARR_GetRecords($Data)
    {
        # $fields Not Required
        # $order Not Required
        # $where Not Required
        # $table Required
        extract($Data);
        global $pdo;
        $FieldList = isset($fields) ? implode(', ', $fields) : '*';
        if (isset($order)) {
            $key = key($order);
            $by = $order[$key];
            $query3 = " ORDER BY {$key} {$by}";
        } else {
            $query3 = "";
        }
        $query1 = sprintf("SELECT %s FROM %s", $FieldList, $table);
        $where = isset($where) ? $this->_where($where) : false;
        if ($where) {
            extract($where);
            $query = $query1 . ' WHERE ' . $query2 . $query3;
            $stmt = $pdo->prepare($query);
            $stmt->execute($data);
        } else {
            $query = $query1 . $query3;
            $stmt = $pdo->prepare($query);
            $stmt->execute();
        }
        return ($stmt->rowCount() > 0) ? $stmt->fetchAll(PDO::FETCH_ASSOC) : false;
    }

    /**
     * @param $table
     * @param $arr
     * @param string $FieldList
     * @return bool
     */
    public function ARR_GetRecord($table, $arr, $FieldList = '*')
    {
        global $pdo;
        if (is_array($FieldList)) {
            $FieldList = implode(',', $FieldList);
        }
        $query1 = sprintf("SELECT %s FROM %s", $FieldList, $table);
        $where = $this->_where($arr);
        if ($where) {
            extract($where);
            $query = $query1 . ' WHERE ' . $query2;
            $stmt = $pdo->prepare($query);
            $stmt->execute($data);
        } else {
            $stmt = $pdo->prepare($query1);
            $stmt->execute();
        }
        return ($stmt->rowCount() > 0) ? $stmt->fetch(PDO::FETCH_ASSOC) : false;
    }

    /**
     * @param $query
     * @param null $data
     * @return bool
     */
    public function ARR_GetRecords_QRY($query, $data = NULL)
    {
        global $pdo;
        $stmt = $pdo->prepare($query);
        (is_null($data)) ? $stmt->execute() : $stmt->execute($data);
        return ($stmt->rowCount() > 0) ? $stmt->fetchAll(PDO::FETCH_ASSOC) : false;
    }

    /**
     * @param $query
     * @param null $data
     * @return bool
     */
    public function ARR_GetRecord_QRY($query, $data = NULL)
    {
        global $pdo;
        $stmt = $pdo->prepare($query);
        (is_null($data)) ? $stmt->execute() : $stmt->execute($data);
        return ($stmt->rowCount() > 0) ? $stmt->fetch(PDO::FETCH_ASSOC) : false;
    }

    /**
     * @param $table
     * @param $in_array
     * @param $condition
     * @param string $fields
     * @return bool
     */
    public function ARR_IN_Cause($table, $in_array, $condition, $fields = '*')
    {
        global $pdo;
        $in = str_repeat('?,', count($in_array) - 1) . '?';
        $sql = "SELECT $fields FROM $table WHERE $condition IN ($in)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($in_array);
        return ($stmt->rowCount() > 0) ? $stmt->fetchAll(PDO::FETCH_ASSOC) : false;
    }


* @param $arr
* @param $table
* @return bool
*/
    public function InsertDataProcess($arr, $table)
    {
        global $pdo;
        $fields = $datas = '';
        $num = 1;
        $count = count($arr);
        foreach ($arr as $key => $value) {
            $fields .= ($num < $count) ? $key . ', ' : $key;
            $datas .= ($num < $count) ? ':' . $key . ', ' : ':' . $key;
            $data[':' . $key] = $value;
            $num++;
        }
        $query = 'INSERT INTO ' . $table . ' ( ' . $fields . ' ) VALUES (' . $datas . ' )';
        $stmt = $pdo->prepare($query);
        $stmt->execute($data);
        return ($pdo->lastInsertId()) ? $pdo->lastInsertId() : false;
    }

    /**
     * @param $where
     * @return string
     */
    public function _wherePrepare_Data($where)
    {
        $y = 1;
        $where_part = ' WHERE ';
        foreach ($where as $value) {
            $where_part .= ($y == count($where)) ? $value . ' = :' . $value : $value . ' = :' . $value . ' AND ';
            $y++;
        }
        return $where_part;
    }

    /**
     * @param $arr
     * @return mixed
     */
    public function _whereExecute_Data($arr)
    {
        foreach ($arr as $key => $value) {
            $data[':' . $key] = $value;
        }
        return $data;
    }

    /**
     * @param $arr
     * @param $table
     * @param null $where
     * @return bool
     */
    public function UpdateDataProcess($arr, $table, $where = NULL)
    {
        global $pdo;
        $keys = array_keys($arr);
        $query_part = sprintf('UPDATE %s SET ', $table);
        $x = 1;
        $key = ($where !== NULL) ? array_diff($keys, $where) : $keys;
        foreach ($key as $value) {
            $query_part .= ($x == count($key)) ? $value . ' = :' . $value : $value . ' = :' . $value . ', ';
            $x++;
        }
        if ($where !== NULL) {
            $where_part = $this->_wherePrepare_Data($where);
        }
        $data = $this->_whereExecute_Data($arr);
        $query = $query_part . $where_part;
        $stmt = $pdo->prepare($query);
        $stmt->execute($data);
        return ($stmt->rowCount() > 0) ? $stmt->rowCount() : false;
    }

    // INSERT DATA ARRANGMENTS
    /**
     * @param $table
     * @param $ins_arr
     * @return array
     */
    public function InsertData($table, $ins_arr)
    {
        $num = 1;
        $field = '';
        $data = '';
        $arr = array();
        $result = array();
        foreach ($ins_arr as $key => $value) {
            $arr[':' . $key] = $value;
            $field .= ($num < count($ins_arr)) ? $key . ', ' : $key;
            $data .= ($num < count($ins_arr)) ? ':' . $key . ', ' : ':' . $key;
            $num++;
        }
        $query = "INSERT INTO $table ( $field ) VALUES ( $data )";
        $result['query'] = $query;
        $result['data'] = $arr;
        return $result;
    }

    /**
     * @param $value
     * @return array
     */
    public function ChangeAmount_Format($value)
    {
        return array_map(function ($arr) {
            return (is_array($arr)) ? $arr : str_replace(',', '', $arr);
        }, $value);
    }

    /**
     * @return int
     */
    public function check_device()
    {
        $useragent = $_SERVER['HTTP_USER_AGENT'];
        return (preg_match('/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|mobile.+firefox|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows ce|xda|xiino/i', $useragent) || preg_match('/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i', substr($useragent, 0, 4))) ? 0 : 1;
    }
}
