<?php

/**
 * Description of ModelBase
 *
 * @author Geru
 */
class ModelBase {

    public static $_fields = array();
    public static $_table = "";
    public static $_key = "";

    /**
     *
     * @var MySQLDB
     */
    public static $_db;

    //public $_id_encrypted;
    //public $_uri;

    public function __construct($data = null) {
        if ($data) {
            $class = get_called_class();
            $class::fetch($this, $data);
        }
    }

    public function generateEncryptedId($id) {
        $this->_id_encrypted = $id;
    }

    public function generateURI() {
        
    }

    public function postFetch($data) {
        
    }

    /* ------------------------------------------------ */

    public static function now_timestamp($add = null) {
        return date("Y-m-d H:i:s");
    }

    public static function fetch($obj, $data) {
        $class = get_called_class();
        foreach ($data as $key => $value) {
            if (array_key_exists($key, $obj))
                $obj->{$key} = $value;
        }
        $obj->postFetch($data);
    }

    public static function filterForDB($obj) {
        $db = self::$_db;
        foreach (self::$_fields as $value) {
            if (!is_null($obj->{$value["name"]})) {
                if ($value["type"] == "int")
                    $obj->{$key} = (int) $obj->{$key};
                else if ($value["type"] == "double")
                    $obj->{$key} = (int) $obj->{$key};
                else if ($value["type"] == "float")
                    $obj->{$key} = (float) $obj->{$key};
                else if ($value["type"] == "varchar")
                    $obj->{$key} = self::filterString($obj->{$key});
                else if ($value["type"] == "text")
                    $obj->{$key} = self::filterString($obj->{$key});
                else
                    $obj->{$key} = self::filterString($obj->{$key});
            }
        }
    }

    public static function filterString($str) {
        return filter_var($str, FILTER_SANITIZE_STRING);
    }

    public static function filterHTML($str, $allowed = "") {
        return strip_tags(filter_var($str, FILTER_SANITIZE_STRING), $allowed);
    }

    public static function filterInteger($int) {
        return (int) $int;
    }

    public static function filterDate($int, $format = 'aaaa-mm-dd') {
        return (int) $int;
    }

    public static function filterFloat($float) {
        return (float) $float;
    }

    static public function parseInt($number) {
        return (int) $number;
    }

    static public function time2str($ts) {
        if (!ctype_digit($ts)) {
            $ts = strtotime($ts);
        }
        $diff = time() - $ts;
        if ($diff == 0) {
            return 'now';
        } elseif ($diff > 0) {
            $day_diff = floor($diff / 86400);
            if ($day_diff == 0) {
                if ($diff < 60)
                    return 'just now';
                if ($diff < 120)
                    return '1 minute ago';
                if ($diff < 3600)
                    return floor($diff / 60) . ' minutes ago';
                if ($diff < 7200)
                    return '1 hour ago';
                if ($diff < 86400)
                    return floor($diff / 3600) . ' hours ago';
            }
            if ($day_diff == 1) {
                return 'Yesterday';
            }
            if ($day_diff < 7) {
                return $day_diff . ' days ago';
            }
            if ($day_diff < 31) {
                return ceil($day_diff / 7) . ' weeks ago';
            }
            if ($day_diff < 60) {
                return 'last month';
            }
            return date('F Y', $ts);
        } else {
            $diff = abs($diff);
            $day_diff = floor($diff / 86400);
            if ($day_diff == 0) {
                if ($diff < 120) {
                    return 'in a minute';
                }
                if ($diff < 3600) {
                    return 'in ' . floor($diff / 60) . ' minutes';
                }
                if ($diff < 7200) {
                    return 'in an hour';
                }
                if ($diff < 86400) {
                    return 'in ' . floor($diff / 3600) . ' hours';
                }
            }
            if ($day_diff == 1) {
                return 'Tomorrow';
            }
            if ($day_diff < 4) {
                return date('l', $ts);
            }
            if ($day_diff < 7 + (7 - date('w'))) {
                return 'next week';
            }
            if (ceil($day_diff / 7) < 4) {
                return 'in ' . ceil($day_diff / 7) . ' weeks';
            }
            if (date('n', $ts) == date('n') + 1) {
                return 'next month';
            }
            return date('F Y', $ts);
        }
    }

    /**
     * 
     * @return MySQLDB
     */
    static function getDB() {

        if (!self::$_db) {
            self::$_db = ModelBase::$_db;
        }
        return self::$_db;
    }

    /* ------------------------------------------------ */

    public static function findById($id) {
        self::getDB();
        $class = get_called_class();
        $id = (int) $id;
        $sql = "SELECT * FROM " . $class::$_table . " WHERE " . $class::$_key . " = $id";
        $r = $class::$_db->query($sql);

        if ($r) {
            $row = $r->fetch_assoc();
        }
        if ($row) {
            return new $class($row);
        }
        return null;
    }

    public static function findBy($condition) {
        self::getDB();
        $class = get_called_class();
        $sql = "SELECT * FROM " . $class::$_table . " WHERE $condition";
        $r = $class::$_db->query($sql);

        if ($r) {
            $row = $r->fetch_assoc();
        }
        if ($row) {
            return new $class($row);
        }
        return null;
    }

    /**
     * 
     * @param string $sql
     * @return mysqli_result
     */
    public static function exec($sql) {
        return self::$_db->query($sql);
    }

    public static function execMulti($sql) {
        return self::$_db->queryMulti($sql);
    }

    public static function save($obj) {
        self::getDB();
        $class = get_called_class();
        if ($obj::$_key != '' && $obj->{$obj::$_key} > 0) {
            $sql = "UPDATE " . $obj::$_table . " SET ";
            foreach ($class::$_fields as $key => $value) {
                $key = $value['name'];
                $value = $obj->{$key};
                if ($key != $obj::$_key) {
                    if (is_null($value)) {
                        $sql.= "$key = NULL, ";
                    } else if (is_numeric($value)) {
                        $sql.= "$key = $value, ";
                    } else {
                        $sql.= "$key = '" . filter_var($value, FILTER_SANITIZE_MAGIC_QUOTES) . "', ";
                    }
                }
            }
            $sql = substr($sql, 0, -2);
            $sql.= " WHERE " . $obj::$_key . "= " . $obj->{$obj::$_key};

            self::logErr($sql);

            $class::$_db->query($sql);

            $err = $class::$_db->getErrorString();
            if ($err != '')
                App::log("MysqlError -> $err");
            return $err == '';
        } else {

            $fields = "(";
            $values = "VALUES(";

            foreach ($class::$_fields as $key => $value) {
                $key = $value['name'];
                $value = $obj->{$key};

                if ($key != $obj::$_key) {
                    if (!is_null($value)) {
                        $fields.=$key . ", ";
                        $values.="'" . filter_var($value, FILTER_SANITIZE_MAGIC_QUOTES) . "', ";
                    }
                }
            }

            $fields = substr($fields, 0, -2) . ")";
            $values = substr($values, 0, -2) . ")";
            $sql = "INSERT INTO " . $obj::$_table . " $fields $values";
            $class::logErr($sql);
            if ($class::$_db->query($sql)) {
                if ($obj::$_key) {
                    $obj->{$obj::$_key} = self::$_db->lastInsertID();
                }
                return true;
            }

            self::logErr("Error al guardar registro | " . json_encode($class::$_db->error_list) . " | -> $sql");
            return false;
        }
    }

    public static function deleteById($id) {
        $class = get_called_class();
        $id = (int) $id;
        self::getDB();
        if ($id > 0) {
            $sql = "DELETE FROM " . $class::$_table . " WHERE " . $class::$_key . " = $id";
            $class::$_db->query($sql);

            $err = $class::$_db->getErrorString();
            if ($err != '' && $err)
                App::log("MysqlError -> $err");
            return $err == '';
        }

        return false;
    }

    public static function deleteByCondition($cond) {
        $class = get_called_class();
        self::getDB();
        $sql = "DELETE FROM " . $class::$_table . " WHERE $cond";
        $class::$_db->query($sql);
        $err = $class::$_db->getErrorString();
        if ($err != '' && $err)
            App::log("MysqlError -> $err");
        return $err == '';
    }

    public static function deleteByIdArray(Array $ids) {
        $class = get_called_class();
        self::getDB();

        if (count($ids) > 0) {
            foreach ($ids as $key => $value) {
                $ids[$key] = (int) $value;
            }
            $sql = "DELETE FROM " . $class::$_table . " WHERE " . $class::$_key . " IN (" . implode(",", $ids) . ")";
            $class::$_db->query($sql);

            $err = $class::$_db->getErrorString();
            if ($err != '' && $err)
                App::log("MysqlError -> $err");
            return $err == '';
        }

        return false;
    }

    /**
     * 
     * @param Int $from
     * @param Int $to
     * @return array
     */
    static public function getList($from = 0, $to = -1, $_sql = "", $inverted = false, $raw = false) {
        $resultado = array();
        $class = get_called_class();
        $class::getDB();
        if ($_sql == "")
            $sql = "SELECT * FROM " . $class::$_table;
        else
            $sql = $_sql;

        self::addLimit($sql, $from, $to);
        $ans = $class::$_db->query($sql);

        if ($ans) {
            if ($raw) {
                while ($row = $ans->fetch_assoc()) {
                    array_push($resultado, $row);
                }
            } else {
                while ($row = $ans->fetch_assoc()) {
                    array_push($resultado, new $class($row));
                }
            }
        } else {
            self::logErr('error ejecutando ' . $sql . ' | ' . json_encode(self::$_db->getError()));
        }

        return $resultado;
    }

    static public function getListWhere($condition = "", $sort = "", $count = -1) {
        $class = get_called_class();
        $sql = "SELECT * FROM " . $class::$_table;
        if ($condition != "") {
            $sql.=" WHERE $condition";
        }
        if ($sort != "") {
            $sql.=" ORDER BY $sort";
        }
        if ($count > 0) {
            $sql.= " LIMIT $count";
        }
        return self::getList(0, -1, $sql);
    }

    /**
     * 
     * @param String $sql
     * @return array
     */
    static public function countRows($where = "") {
        $resultado = array();
        $class = get_called_class();
        $class::getDB();
        $sql = "SELECT count(" . $class::$_key . ") total FROM " . $class::$_table;
        if ($where != "") {
            $sql.= " WHERE $where";
        }


        self::addLimit($sql, 0, 1);

        $rs = $class::$_db->query($sql);

        if (!$rs) {
            self::logErr("SQL error -> $sql");
            return null;
        }
        if ($rs->num_rows > 0)
            $ans = $rs->fetch_assoc();
        return $ans ? (int) $ans['total'] : 0;
    }

    /**
     * 
     * @param String $sql
     * @return array
     */
    static public function getOne($_sql = "") {
        $resultado = array();
        $class = get_called_class();
        $class::getDB();
        if ($_sql == "")
            $sql = "SELECT * FROM " . $class::$_table;
        else
            $sql = $_sql;

        self::addLimit($sql, 0, 1);

        $rs = $class::$_db->query($sql);

        if (!$rs) {
            self::logErr("SQL error -> $sql");
            return null;
        }
        if ($rs->num_rows > 0)
            $ans = $rs->fetch_assoc();
        return $ans ? new $class($ans) : null;
    }

    /**
     * 
     * @param String $sql
     * @return array
     */
    static public function getOneWhere($condition) {
        $class = get_called_class();
        $class::getDB();
        $sql = "SELECT * FROM " . $class::$_table . " WHERE $condition";

        self::addLimit($sql, 0, 1);

        $rs = $class::$_db->query($sql);

        if (!$rs) {
            self::logErr("SQL error -> $sql");
            return null;
        }
        $ans = null;
        if ($rs->num_rows > 0)
            $ans = $rs->fetch_assoc();
        return $ans ? new $class($ans) : null;
    }

    static public function addLimit(&$sql, $from, $to) {
        if ($from > 0) {
            if ($to > 0) {
                $sql.=" LIMIT $from, $to";
            }
        } else if ($to > 0) {
            $sql.=" LIMIT $to";
        }
    }

    /* --------------------------------------------------------- */

    static public function logErr($str = "") {
        $clase = get_called_class();
        Utils::logInFile("Model_$clase", self::now_timestamp() . " " . $str);
    }

    static public function generateModels(array $tables = null) {

        $db = ModelBase::getDB();
        if (!is_dir(PATH_MODELS . "/base")) {
            mkdir(PATH_MODELS . "/base");
        }
        if ($tables) {
            $sql = "SHOW TABLES WHERE Tables_in_" . $db->database . " IN ('" . implode("','", $tables) . "')";
        } else {
            $sql = "SHOW TABLES";
        }
        $tables = $db->query($sql);

        $class_model = "<?php \nclass [CLASE]Base extends ModelBase {\n\tpublic static \$_table = '[TABLA]';\n\tpublic static \$_key = '[KEY]';\n\tpublic static \$_fields = array([FIELDS]);\n\n[COLS]\n\n}\n";
        $class_custom_model = "<?php \nclass [CLASE] extends [CLASE]Base {\n\t\n\n}\n";
        $class_replace = array('[CLASE]', '[TABLA]', '[KEY]', '[COLS]', '[FIELDS]');
        if (!$tables) {
            echo json_encode($db->getError());
            return;
        };
        $all = array();
        while ($table = $tables->fetch_array()) {
            $tbl = $table[0];
            $className = str_replace(' ', '', ucwords(str_replace('_', " ", $tbl)));
            $base_file = PATH_MODELS . "/base/" . $className . "Base.php";
            $custom_file = PATH_MODELS . "/$className.php";

            $cols = $db->query("show columns from $tbl");
            $col_str = "";
            $fld_str = "";
            $key = '';
            $rr = new mysqli_result();
            //"([a-z]+)\(([0-9]+)\) ([a-z]+)"
            $return = array();
            while ($col = $cols->fetch_array()) {
                $col_str.="\tpublic \$" . $col["Field"] . ";\n";
                $type = $col['Type'];
                $type_parts = array();

                preg_match("|([a-z]+)\(([0-9a-z\',]*)\)*|", $type, $type_parts);
                if (count($type_parts) == 0) {
                    $type_parts[0] = "";
                    $type_parts[1] = $type;
                    $type_parts[2] = 0;
                    $type_parts[3] = "";
                }
                $type_parts[4] = ($col['Null'] == 'NO' ? 1 : 0);

                $fld_str.="array('name'=>'$col[0]','type'=>'$type_parts[1]','size'=>$type_parts[2],'not_null'=>$type_parts[4]),";
                if ($col[3] == 'PRI') {
                    if ($key != '')
                        $key = '-1';
                    else
                        $key = $col[0];
                }
                $return[] = $col;
            }


            if ($key == '-1')
                $key = '';

            $fld_str = substr($fld_str, 0, -1);
            $file_str = str_replace($class_replace, array($className, $tbl, $key, $col_str, $fld_str), $class_model);

            file_put_contents($base_file, $file_str);
            echo "$className Base Generado<br>";

            if (!file_exists($custom_file)) {
                $file_custom_str = str_replace($class_replace, array($className, $tbl, $key, $col_str, ''), $class_custom_model);
                file_put_contents($custom_file, $file_custom_str);
                echo "$className Custom Generado<br>";
            } else {
                echo "$className No se ha generado<br>";
            }
        }
        //echo json_encode($return);
        //return;

        return;
    }

}
