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
    public $user_type;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create() {
        $query = "INSERT INTO " . $this->table_name . " 
                  SET email=:email, password=:password, user_type=:user_type";
        
        $stmt = $this->conn->prepare($query);
        
        $this->password = password_hash($this->password, PASSWORD_DEFAULT);
        
        $stmt->bindParam(":email", $this->email);
        $stmt->bindParam(":password", $this->password);
        $stmt->bindParam(":user_type", $this->user_type);
        
        return $stmt->execute();
    }

    public function login() {
        $query = "SELECT id, email, password, user_type FROM " . $this->table_name . " 
                  WHERE email = :email LIMIT 0,1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':email', $this->email);
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if($row && password_verify($this->password, $row['password'])) {
            $this->id = $row['id'];
            $this->user_type = $row['user_type'];
            return true;
        }
        return false;
    }
}
?>