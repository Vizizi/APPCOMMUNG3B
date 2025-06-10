// ========================
// USER.PHP (Model)
// ========================
<?php
class User {
    private $conn;
    private $table_name = "users";

    public $id;
    public $email;
    public $password;
    public $created_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create() {
        $query = "INSERT INTO " . $this->table_name . " 
                  SET email=:email, password=:password";
        
        $stmt = $this->conn->prepare($query);
        
        $this->password = password_hash($this->password, PASSWORD_DEFAULT);
        
        $stmt->bindParam(":email", $this->email);
        $stmt->bindParam(":password", $this->password);
        
        return $stmt->execute();
    }

    public function login() {
        $query = "SELECT id, email, password FROM " . $this->table_name . " 
                  WHERE email = :email LIMIT 0,1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':email', $this->email);
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if($row && password_verify($this->password, $row['password'])) {
            $this->id = $row['id'];
            return true;
        }
        return false;
    }
}
?>
