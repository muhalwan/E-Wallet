<?php
// 'user' object
class User{
 
    // database connection and table name
    private $conn;
    private $table_name = "useracc";
 
    // object properties
    public $id_user;
    public $nama;
    public $email;
    public $kata_sandi;
    public $role;
 
    // constructor
    public function __construct($db){
        $this->conn = $db; 
    }
 
    // create new user record
    function create(){
        // insert query
        $query = "INSERT INTO " . $this->table_name . "
                SET
                    nama = :nama,
                    email = :email,
                    kata_sandi = :kata_sandi,
                    role = :role";
    
        // prepare the query
        $stmt = $this->conn->prepare($query);
    
        // sanitize
        $this->nama=htmlspecialchars(strip_tags($this->nama));
        $this->email=htmlspecialchars(strip_tags($this->email));
        $this->kata_sandi=htmlspecialchars(strip_tags($this->kata_sandi));
        $this->role=htmlspecialchars(strip_tags($this->role));
    
        // bind the values
        $stmt->bindParam(':nama', $this->nama);
        $stmt->bindParam(':email', $this->email);
        $stmt->bindParam(':role', $this->role);
    
        // hash the password before saving to database
        $kata_sandi_hash = password_hash($this->kata_sandi, PASSWORD_BCRYPT);
        $stmt->bindParam(':kata_sandi', $kata_sandi_hash);
    
        // execute the query, also check if query was successful
        if($stmt->execute()){
            return true;
        }
    
        return false;
    }

    function emailExists(){
        $query = "SELECT id_user, nama, kata_sandi, role
                FROM " . $this->table_name . "
                WHERE email = ?
                LIMIT 0,1";
    
        $stmt = $this->conn->prepare( $query );
    
        $this->email=htmlspecialchars(strip_tags($this->email));
    
        $stmt->bindParam(1, $this->email);
    
        $stmt->execute();
    
        $num = $stmt->rowCount();
    
        if($num>0){
    
            // get record details / values
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
    
            // assign values to object properties
            $this->id_user = $row['id_user'];
            $this->nama = $row['nama'];
            $this->kata_sandi = $row['kata_sandi'];
            $this->role = $row['role'];
    
            // return true because email exists in the database
            return true;
        }
    
        // return false if email does not exist in the database
        return false;
    }

    public function update(){
        // if password needs to be updated
        $kata_sandi_set=!empty($this->kata_sandi) ? ", kata_sandi = :kata_sandi" : "";
    
        // if no posted password, do not update the password
        $query = "UPDATE " . $this->table_name . "
                SET
                    nama = :nama,
                    email = :email
                    {$kata_sandi_set}
                WHERE id_user = :id_user";
    
        // prepare the query
        $stmt = $this->conn->prepare($query);
    
        // sanitize
        $this->nama=htmlspecialchars(strip_tags($this->nama));
        $this->email=htmlspecialchars(strip_tags($this->email));
    
        // bind the values from the form
        $stmt->bindParam(':nama', $this->nama);
        $stmt->bindParam(':email', $this->email);
    
        // hash the password before saving to database
        if(!empty($this->kata_sandi)){
            $this->kata_sandi=htmlspecialchars(strip_tags($this->kata_sandi));
            $kata_sandi_hash = password_hash($this->kata_sandi, PASSWORD_BCRYPT);
            $stmt->bindParam(':kata_sandi', $kata_sandi_hash);
        }
    
        // unique ID of record to be edited
        $stmt->bindParam(':id_user', $this->id_user);
    
        // execute the query
        if($stmt->execute()){
            return true;
        }
    
        return false;
    }

    public function getAllUser(){
        $query = "SELECT * FROM " . $this->table_name;

        // prepare the query
        $stmt = $this->conn->prepare($query);
    
        // execute the query
        if($stmt->execute()){
            return $stmt;
        }
    
        return false;
    }

    public function getUser(){
        $query = "SELECT * FROM " . $this->table_name . "
                WHERE id_user = ? LIMIT 1";

         // prepare the query
         $stmt = $this->conn->prepare($query);
    
        // execute the query
        if($stmt->execute([$this->id_user])){
            return $stmt;
        }
    
        return false;
    }

    public function delete(){
        $query = "DELETE FROM " . $this->table_name . "
                WHERE id_user = :id_user";

        // prepare the query
        $stmt = $this->conn->prepare($query);
        
        // unique ID of record to be edited
        $stmt->bindParam(':id_user', $this->id_user);
    
        // execute the query
        if($stmt->execute()){
            return true;
        }
    
        return false;
    }

}