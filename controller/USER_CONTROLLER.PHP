// ========================
// USER_CONTROLLER.PHP
// ========================
<?php

class UserController {
    private $db;
    private $user;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->user = new User($this->db);
    }

    public function register() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $input = json_decode(file_get_contents('php://input'), true);
            
            $this->user->email = $input['email'];
            $this->user->password = $input['password'];
            
            if ($this->user->create()) {
                http_response_code(201);
                echo json_encode(array("message" => "Utilisateur créé."));
            } else {
                http_response_code(503);
                echo json_encode(array("message" => "Impossible de créer l'utilisateur."));
            }
        }
    }

    public function login() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $input = json_decode(file_get_contents('php://input'), true);
            
            $this->user->email = $input['email'];
            $this->user->password = $input['password'];
            
            if ($this->user->login()) {
                session_start();
                $_SESSION['user_id'] = $this->user->id;
                $_SESSION['user_email'] = $this->user->email;
                
                echo json_encode(array(
                    "message" => "Connexion réussie.",
                    "user_id" => $this->user->id
                ));
            } else {
                http_response_code(401);
                echo json_encode(array("message" => "Email ou mot de passe incorrect."));
            }
        }
    }

    public function logout() {
        session_start();
        session_destroy();
        echo json_encode(array("message" => "Déconnexion réussie."));
    }
}