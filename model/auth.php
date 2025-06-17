<?php
$host = 'localhost';
$db   = 'serre_connectee';
$user = 'root'; // adapte si nécessaire
$pass = '';     // mot de passe selon ta config
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];

    if ($action === 'register') {
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirmPassword'] ?? '';

        if ($password !== $confirmPassword) {
            header('Location: ../v/Index.html?error=password_mismatch');
            exit;
        }

        // Hachage du mot de passe
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        // Insertion en BDD
        $stmt = $pdo->prepare("INSERT INTO users (email, mot_de_passe) VALUES (?, ?)");
        try {
            $stmt->execute([$email, $hashedPassword]);
            header('Location: ../v/Index.html?success=1');
        } catch (PDOException $e) {
            header('Location: ../v/Index.html?error=email_exists');
        }
    }
}
