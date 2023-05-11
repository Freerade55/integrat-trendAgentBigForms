<?php

require_once($_SERVER['DOCUMENT_ROOT'] . '/api/MySQL/src/PDO.class.php');

class ConnectMySQL
{

    private $db_host = null;
    private $db_port = null;
    private $db_name = null;
    private $db_user = null;
    private $db_pass = null;

    public $DB = null;

    private $create_table;

    function __construct($request)
    {

        $this->db_host = '';
        $this->db_port = 3306;
        $this->db_name = '';
        $this->db_user = '';
        $this->db_pass = '';
        $this->DB = new Db($this->db_host, $this->db_port, $this->db_name, $this->db_user, $this->db_pass);

    }

    /*
     * @params [ string ] nameTable
     * return void
     * */
    public function createTableUsers(string $nameTable){

        try {
            $this->DB->query(
                "CREATE TABLE IF NOT EXISTS `$nameTable` (
                    `id` INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                    `user_name` varchar(255),
                    `user_id` int(30),
                    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                )"
            );
        }
        catch (Exception $exception){
            var_dump($exception);
        }

        $this->DB->closeConnection();
    }

    public function deleteTable(string $nameTable){

        try {
            $this->DB->query("DROP TABLE IF EXISTS `$nameTable`;");
        }
        catch (Exception $exception){
            var_dump($exception);
        }

        $this->DB->closeConnection();
    }

    public function insertData(string $nameTable, string $nameColumnSet, string $nameColumnWhere, array $params){

        try {
//            $this->DB->query("SELECT * FROM `$nameTable` WHERE $nameColumn=?", array('77'));

            $this->DB->query("
                UPDATE `$nameTable` 
                SET `$nameColumnSet` = :status 
                WHERE `$nameColumnWhere` = :lead_id",
                ["lead_id"=>"32","status"=>"03"]);
        }
        catch (Exception $exception){
            var_dump($exception);
        }

        $this->DB->closeConnection();
    }

    public function updateData(string $nameTable){

        try {
            $this->DB->query("DROP TABLE IF EXISTS `$nameTable`;");
        }
        catch (Exception $exception){
            var_dump($exception);
        }

        $this->DB->closeConnection();
    }
}