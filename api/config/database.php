<?php
// used to get mysql database connection
class Database{
 
    // specify your own database credentials
    private $servername = "localhost";
    private $db_name = "pweb";
    private $username = "root";
    private $password = "";
    public $conn;
 
    // get the database connection
    public function getConnection(){
        $this->conn = null;
 
        try{
            $this->conn = new PDO("mysql:host=" . $this->servername . ";dbname=" . $this->db_name, $this->username, $this->password);
        }catch(PDOException $exception){
            echo "Connection error: " . $exception->getMessage();
        }
 
        return $this->conn;
    }
}
?>