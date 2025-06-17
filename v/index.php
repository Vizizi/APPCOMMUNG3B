<?php
session_start();
require_once '../model/config.php'; // Ensure this path is correct relative to auth.php

// Initialisation des variables d'erreur et de succès
$email_err = $password_err = $confirm_password_err = "";
$login_err = "";
$registration_success = "";
$email = ""; // Keep email value for pre-filling form if error

// Check for general error or success messages from redirects
if (isset($_GET['registered']) && $_GET['registered'] == 'true') {
    $registration_success = "Votre compte a été créé avec succès. Vous pouvez maintenant vous connecter.";
} elseif (isset($_GET['error'])) {
    if ($_GET['error'] === 'email_exists') {
        $email_err = "Cette adresse e-mail est déjà utilisée.";
    } elseif ($_GET['error'] === 'insert_failed') {
        // More generic error if insert failed
        $email_err = "Une erreur est survenue lors de l'inscription. Veuillez réessayer.";
    }
} elseif (isset($_GET['login_error']) && $_GET['login_error'] == '1') {
    $login_err = "Email ou mot de passe incorrect.";
} elseif (isset($_GET['email'])) {
    $email_err = urldecode($_GET['email']);
} elseif (isset($_GET['password'])) {
    $password_err = urldecode($_GET['password']);
} elseif (isset($_GET['confirm'])) {
    $confirm_password_err = urldecode($_GET['confirm']);
}


// --- Traitement des soumissions de formulaires ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['action']) && $_POST['action'] === 'register') {
        // --- Inscription ---
        $email = trim($_POST["email"]);
        $password = trim($_POST["password"]);
        $confirm_password = trim($_POST["confirmPassword"]);

        // Validation email
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $email_err = "Veuillez entrer une adresse e-mail valide.";
        }

        // Validation mot de passe
        if (empty($password) || strlen($password) < 6) {
            $password_err = "Le mot de passe doit contenir au moins 6 caractères.";
        }

        // Vérification confirmation
        if ($password !== $confirm_password) {
            $confirm_password_err = "Les mots de passe ne correspondent pas.";
        }

        // S'il n'y a pas d'erreurs de validation
        if (empty($email_err) && empty($password_err) && empty($confirm_password_err)) {
            // Vérifier si l'utilisateur existe déjà
            $sql = "SELECT id FROM users WHERE email = ?";
            if ($stmt = $mysqli->prepare($sql)) {
                $stmt->bind_param("s", $email);
                $stmt->execute();
                $stmt->store_result();

                if ($stmt->num_rows > 0) {
                    $email_err = "Cette adresse e-mail est déjà utilisée.";
                    // Don't redirect, let the page re-render with the error
                }
                $stmt->close();
            }

            // If still no errors after database check
            if (empty($email_err)) {
                // Insérer le nouvel utilisateur
                $sql = "INSERT INTO users (email, password, created_at) VALUES (?, ?, NOW())";
                if ($stmt = $mysqli->prepare($sql)) {
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                    $stmt->bind_param("ss", $email, $hashed_password);
                    if ($stmt->execute()) {
                        header("Location: " . htmlspecialchars($_SERVER["PHP_SELF"]) . "?registered=true"); // Redirect to self with success
                        exit();
                    } else {
                        // Handle database insertion error
                        // For security, avoid revealing too much about database issues to the user
                        $email_err = "Une erreur est survenue lors de l'inscription. Veuillez réessayer.";
                    }
                    $stmt->close();
                }
            }
        }
        // If there were validation errors, the variables are set, and the form will be displayed below
        // No explicit redirection here for validation errors; they are displayed on the same page.

    } elseif (isset($_POST['action']) && $_POST['action'] === 'login') {
        // --- Connexion ---
        $email = trim($_POST["email"]);
        $password = trim($_POST["password"]);

        // Validate email
        if (empty($email)) {
            $login_err = "Veuillez entrer votre adresse e-mail.";
        }

        // Validate password
        if (empty($password)) {
            $login_err = "Veuillez entrer votre mot de passe.";
        }

        if (empty($login_err)) {
            $sql = "SELECT id, email, password FROM users WHERE email = ?";
            if ($stmt = $mysqli->prepare($sql)) {
                $stmt->bind_param("s", $email);
                if ($stmt->execute()) {
                    $stmt->store_result();

                    if ($stmt->num_rows == 1) {
                        $stmt->bind_result($id, $email_db, $hashed_password);
                        if ($stmt->fetch()) {
                            if (password_verify($password, $hashed_password)) {
                                session_regenerate_id(true);
                                $_SESSION["loggedin"] = true;
                                $_SESSION["id"] = $id;
                                $_SESSION["email"] = $email_db;
                                header("Location: ../view/Site.html"); // Redirect to your main site page
                                exit();
                            } else {
                                // Password is incorrect
                                $login_err = "Email ou mot de passe incorrect.";
                            }
                        }
                    } else {
                        // Email not found
                        $login_err = "Email ou mot de passe incorrect.";
                    }
                } else {
                    // Database execution error
                    $login_err = "Une erreur est survenue lors de la connexion. Veuillez réessayer.";
                }
                $stmt->close();
            } else {
                // Prepare statement error
                $login_err = "Une erreur est survenue lors de la connexion. Veuillez réessayer.";
            }
        }
    }
}

$mysqli->close();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Connexion / Inscription - SerreConnect</title>
    <link rel="stylesheet" href="../view/styles.css"> <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

       
</head>
<body>

    <div id="auth-section">
        <div class="auth-container">
            <h2 class="text-center mb-20">
                <i class="fas fa-leaf"></i> Accès Serre Connectée
            </h2>

            <?php if (!empty($registration_success)): ?>
                <p class="success-message"><?php echo $registration_success; ?></p>
            <?php endif; ?>

            <div id="login-form">
                <h3 class="mb-20">Connexion</h3>
                <?php if (!empty($login_err)): ?>
                    <p class="error-message"><?php echo $login_err; ?></p>
                <?php endif; ?>
                <form id="loginForm" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST">
                    <input type="hidden" name="action" value="login">
                    <div class="form-group">
                        <label for="loginEmail">
                            <i class="fas fa-envelope"></i> Email
                        </label>
                        <input type="email" id="loginEmail" name="email" value="<?php echo htmlspecialchars($email ?? ''); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="loginPassword">
                            <i class="fas fa-lock"></i> Mot de passe
                        </label>
                        <input type="password" id="loginPassword" name="password" required>
                    </div>
                    <button type="submit" class="btn">
                        <i class="fas fa-sign-in-alt"></i> Se connecter
                    </button>
                    <button type="button" class="btn btn-secondary" onclick="showRegister()">
                        <i class="fas fa-user-plus"></i> S'inscrire
                    </button>
                </form>
            </div>

            <div id="register-form" class="hidden">
                <h3 class="mb-20">Inscription</h3>
                <form id="registerForm" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST">
                    <input type="hidden" name="action" value="register">
                    <div class="form-group">
                        <label for="registerEmail">
                            <i class="fas fa-envelope"></i> Email
                        </label>
                        <input type="email" id="registerEmail" name="email" value="<?php echo htmlspecialchars($email ?? ''); ?>" required>
                        <p class="error-message"><?php echo $email_err; ?></p>
                    </div>
                    <div class="form-group">
                        <label for="registerPassword">
                            <i class="fas fa-lock"></i> Mot de passe
                        </label>
                        <input type="password" id="registerPassword" name="password" required>
                        <p class="error-message"><?php echo $password_err; ?></p>
                    </div>
                    <div class="form-group">
                        <label for="confirmPassword">
                            <i class="fas fa-lock"></i> Confirmer le mot de passe
                        </label>
                        <input type="password" id="confirmPassword" name="confirmPassword" required>
                        <p class="error-message"><?php echo $confirm_password_err; ?></p>
                    </div>
                    <button type="submit" class="btn">
                        <i class="fas fa-user-plus"></i> S'inscrire
                    </button>
                    <button type="button" class="btn btn-secondary" onclick="showLogin()">
                        <i class="fas fa-sign-in-alt"></i> Déjà inscrit ?
                    </button>
                </form>
            </div>
        </div>
    </div>

    <script>
        function showRegister() {
            document.getElementById('login-form').classList.add('hidden');
            document.getElementById('register-form').classList.remove('hidden');
        }

        function showLogin() {
            document.getElementById('register-form').classList.add('hidden');
            document.getElementById('login-form').classList.remove('hidden');
        }

        // Client-side password confirmation validation
        document.getElementById('registerForm').addEventListener('submit', function (e) {
            const pwd = document.getElementById('registerPassword').value;
            const confirm = document.getElementById('confirmPassword').value;
            if (pwd !== confirm) {
                e.preventDefault();
                alert('Les mots de passe ne correspondent pas.');
            }
        });

        // Show registration form if there are registration errors or a success message
        document.addEventListener('DOMContentLoaded', function() {
            const urlParams = new URLSearchParams(window.location.search);
            const isRegistering = <?php echo json_encode(!empty($email_err) || !empty($password_err) || !empty($confirm_password_err) || (!empty($_POST['action']) && $_POST['action'] === 'register')); ?>;
            const isRegisteredSuccess = urlParams.get('registered') === 'true';

            if (isRegistering && !isRegisteredSuccess) { // Show register form if there are registration errors but not a success
                showRegister();
            } else if (isRegisteredSuccess) { // Always default to login form after successful registration
                showLogin();
            } else if (urlParams.get('login_error') === '1') { // If there's a login error, stay on login form
                showLogin();
            } else {
                // Default to login form when page loads without specific actions/errors
                showLogin();
            }
        });

    </script>

</body>
</html>