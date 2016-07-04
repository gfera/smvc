<?php

class MySQLDB {

    /**
     * 
     * @var mysqli
     */
    private $cnx;
    private $rs = array();
    public $database;

    public function __construct($host, $username, $password, $db) {
        $this->database = $db;
        $this->cnx = new mysqli($host, $username, $password, $db);
        $this->cnx->set_charset('utf8');
    }

    /**
     * 
     * @param string $sql
     * @return mysqli_result
     */
    public function query($sql) {
        $c = $this->cnx;
        if (!$c)
            return null;
        $r = $c->query($sql);

        if (!$r)
            trigger_error("Error: $sql");
        else if (!is_bool($r))
            array_push($this->rs, $r);

        return $r;
    }

    public function queryMulti($sql) {
        $c = $this->cnx;
        if (!$c)
            return null;
        $ret = array();
        $c->multi_query($sql);
        do {
            $rs = $c->store_result();
            $rs_all = array();
            while ($row = $rs->fetch_assoc()) {
                $rs_all[] = $row;
            };
            $rs->free();
            $ret[] = array('success' => $rs_all, 'error' => $c->error, "insert_id" => $c->insert_id);
            
        } while ($c->next_result());
        if($c->errno>0){
            $ret[] = array('success' => null, 'error' => $c->error);
        }
        return $ret;
    }

    public function filter($str) {
        $c = $this->cnx;
        return $c ? $c->escape_string($str) : $str;
    }

    public function refresh() {
        if ($this->cnx)
            $this->cnx->refresh();
    }

    public function affectedRows() {
        $c = $this->cnx;
        return $c ? $c->affected_rows : 0;
    }

    public function lastInsertID() {
        return $this->cnx ? $this->cnx->insert_id : 0;
    }

    public function close() {
        while ($rs = array_pop($this->rs)) {
            try {
                if ($rs)
                    $rs->free();
            } catch (Exception $e) {
                
            }
        }
        if ($this->cnx)
            $this->cnx->close();
        $this->cnx = null;
    }

    public function getDateNow() {
        return date('Y-m-d H:i:s', time());
    }

    /**
     * 
     * @return Array
     */
    public function getError() {
        return $this->cnx->error_list;
    }

    /**
     * 
     * @return String
     */
    public function getErrorString() {
        return $this->cnx->error;
    }

}

?>