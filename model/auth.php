<?php
// auth.php
require_once 'db_connect.php';

session_start();

// Fonction pour vérifier les identifiants
function authenticateUser($email, $password) {
    global $pdo;
    
    $stmt = $pdo->prepare("SELECT id, email, password, user_type FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    
    if ($user && password_verify($password, $user['password'])) {
        return $user;
    }
    return false;
}

// Fonction pour enregistrer un nouvel utilisateur
function registerUser($email, $password) {
    global $pdo;
    
    // Vérifier si l'email existe déjà
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    
    if ($stmt->fetch()) {
        return false; // Email déjà existant
    }
    
    // Hacher le mot de passe
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
    $stmt = $pdo->prepare("INSERT INTO users (email, password, user_type) VALUES (?, ?, 'user')");
    $stmt->execute([$email, $hashed_password]);
    
    return $pdo->lastInsertId();
}

// Traitement des requêtes
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    // Connexion
    if ($_POST['action'] === 'login') {
        $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
        $password = $_POST['password'];
        
        $user = authenticateUser($email, $password);
        
        if ($user) {
            $_SESSION['user'] = [
                'id' => $user['id'],
                'email' => $user['email'],
                'user_type' => $user['user_type']
            ];
            header('Location: Site.html');
            exit;
        } else {
            header('Location: index.html?error=invalid_credentials');
            exit;
        }
    }
    
    // Inscription
    if ($_POST['action'] === 'register') {
        $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
        $password = $_POST['password'];
        $confirmPassword = $_POST['confirmPassword'];
        
        if ($password !== $confirmPassword) {
            header('Location: index.html?error=password_mismatch');
            exit;
        }
        
        $userId = registerUser($email, $password);
        
        if ($userId) {
            // Connecter directement l'utilisateur après inscription
            $_SESSION['user'] = [
                'id' => $userId,
                'email' => $email,
                'user_type' => 'user'
            ];
            header('Location: Site.html');
            exit;
        } else {
            header('Location: index.html?error=email_exists');
            exit;
        }
    }
    
    // Déconnexion
    if ($_POST['action'] === 'logout') {
        session_destroy();
        header('Location: index.html');
        exit;
    }
}
?>