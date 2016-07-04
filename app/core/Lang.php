<?php 

class Lang{
    public $host_language;
    public $language_code;
    public $language_id;
    public $default_language_id;
    
    /**
     *
     * @var MySQLDB 
     */
    private $_db;
    
    public function __construct(){
        $this->default_language_id = 2;
        $this->host_language = substr($_SERVER["HTTP_ACCEPT_LANGUAGE"],0,2);
        $this->language_id = $this->default_language_id;
        $this->language_code = $this->host_language;
    }
    public function setDB(MySQLDB $db){
        $this->_db = $db;
    }
    public function loadLanguage(){
        $sql = "SELECT languageID FROM languages WHERE language_identifier = '$this->host_language' AND approved = 'Y'";
        $rs = $this->_db->query($sql);
        if($rs){
            $data = $rs->fetch_assoc();
            $this->language_id = $data['languageID'];
            $this->language_code = $data['language_identifier'];
        } else {
            $this->language_id = $this->default_language_id;
            $this->language_code = 'en';
        }
    }
}
?>