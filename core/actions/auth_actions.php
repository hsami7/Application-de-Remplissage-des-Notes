<?php

/**
 * Gère le processus de connexion de l'utilisateur.
 */
function handle_login() {
    // Vérifier si la requête est une requête POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        // Rediriger vers la page de connexion si ce n'est pas le cas
        header('Location: ' . APP_URL . '/index.php?page=login');
        exit;
    }

    // --- 1. Récupération et validation des données du formulaire ---
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';

    error_log("DEBUG: handle_login() - Attempting login for email: '$email'");

    if (empty($email) || empty($password)) {
        $_SESSION['error_message'] = "Veuillez remplir tous les champs.";
        header('Location: ' . APP_URL . '/index.php?page=login');
        exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error_message'] = "L'adresse e-mail n'est pas valide.";
        header('Location: ' . APP_URL . '/index.php?page=login');
        exit;
    }

    // --- 2. Connexion à la base de données ---
    $pdo = getDBConnection();

    // --- 3. Recherche de l'utilisateur ---
    try {
        $sql = "SELECT id, nom, prenom, email, mot_de_passe, role FROM utilisateurs WHERE email = :email";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch();

        error_log("DEBUG: handle_login() - User fetch result: " . ($user ? "User found with ID " . $user['id'] : "User not found"));

        // --- 4. Vérification de l'utilisateur et du mot de passe ---
        if ($user && password_verify($password, $user['mot_de_passe'])) {
            // --- 5. Authentification réussie ---
            
            // Régénérer l'ID de session pour la sécurité
            session_regenerate_id(true);

            // Stocker les informations de l'utilisateur dans la session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_role'] = $user['role'];
            $_SESSION['user_name'] = $user['prenom'] . ' ' . $user['nom'];
            $_SESSION['authenticated'] = true;

            // --- 6. Redirection vers le tableau de bord approprié ---
            // Determine dashboard based on role
            $dashboard_page = 'login'; // Default to login if role is not recognized
            switch ($user['role']) {
                case 'admin':
                    $dashboard_page = 'dashboard_admin';
                    break;
                case 'professeur':
                    $dashboard_page = 'dashboard_prof';
                    break;
                case 'etudiant':
                    $dashboard_page = 'dashboard_student';
                    break;
            }
            $redirect_url = APP_URL . "/index.php?page={$dashboard_page}";
            
            header('Location: ' . $redirect_url);
            exit;
        } else {
            // Utilisateur non trouvé ou mot de passe incorrect
            $_SESSION['error_message'] = "L'adresse e-mail ou le mot de passe est incorrect.";
            header('Location: ' . APP_URL . '/index.php?page=login');
            exit;
        }

    } catch (PDOException $e) {
        // Gérer les erreurs de base de données
        error_log("Erreur d'authentification : " . $e->getMessage());
        $_SESSION['error_message'] = "Une erreur est survenue lors de la tentative de connexion. Veuillez réessayer.";
        header('Location: ' . APP_URL . '/index.php?page=login');
        exit;
    }
}

/**
 * Gère le changement de mot de passe de l'utilisateur.
 */
function handle_change_password() {
    // Validation
    $user_id = $_SESSION['user_id'];
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        $_SESSION['error_message'] = "Tous les champs sont obligatoires.";
        header('Location: ' . APP_URL . '/index.php?page=change_password');
        exit;
    }

    if ($new_password !== $confirm_password) {
        $_SESSION['error_message'] = "Le nouveau mot de passe et sa confirmation ne correspondent pas.";
        header('Location: ' . APP_URL . '/index.php?page=change_password');
        exit;
    }

    try {
        $pdo = getDBConnection();

        // Vérifier le mot de passe actuel
        $stmt = $pdo->prepare("SELECT mot_de_passe FROM utilisateurs WHERE id = ?");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($current_password, $user['mot_de_passe'])) {
            $_SESSION['error_message'] = "Le mot de passe actuel est incorrect.";
            header('Location: ' . APP_URL . '/index.php?page=change_password');
            exit;
        }

        // Mettre à jour le mot de passe
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $update_stmt = $pdo->prepare("UPDATE utilisateurs SET mot_de_passe = ? WHERE id = ?");
        $update_stmt->execute([$hashed_password, $user_id]);

        $_SESSION['success_message'] = "Votre mot de passe a été changé avec succès.";
        header('Location: ' . APP_URL . '/index.php?page=change_password');
        exit;

    } catch (PDOException $e) {
        $_SESSION['error_message'] = "Erreur BDD: " . (DEBUG_MODE ? $e->getMessage() : "Contactez un admin.");
        header('Location: ' . APP_URL . '/index.php?page=change_password');
        exit;
    }
}