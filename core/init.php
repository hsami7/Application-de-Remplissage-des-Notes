<?php
// --- Initialisation Globale de l'Application ---

// 1. Démarrer la session
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// 2. Inclure les fichiers de configuration et les fonctions essentielles
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/functions/db_connect.php';




/**
 * Gère le contrôle d'accès et l'autorisation pour les pages.
 *
 * @param array $allowed_roles Les rôles qui sont autorisés à voir la page.
 *                             (ex: ['admin', 'professeur']).
 *                             Si le tableau est vide, seul un utilisateur authentifié est requis.
 */
function authorize_user(array $allowed_roles = []) {
    // 1. Vérifier si l'utilisateur est authentifié
    if (!isset($_SESSION['authenticated']) || $_SESSION['authenticated'] !== true) {
        $_SESSION['error_message'] = "Vous devez être connecté pour accéder à cette page.";
        header('Location: ' . APP_URL . '/index.php?page=login');
        exit;
    }

    // 2. Si des rôles spécifiques sont requis, vérifier le rôle de l'utilisateur
    if (!empty($allowed_roles)) {
        $user_role = $_SESSION['user_role'] ?? null;
        if (!in_array($user_role, $allowed_roles)) {
            $_SESSION['error_message'] = "Accès non autorisé. Vous n'avez pas les permissions nécessaires.";
            // Rediriger vers le tableau de bord de l'utilisateur ou une page d'erreur
            $dashboard_map = [
                'admin' => 'dashboard_admin',
                'professeur' => 'dashboard_prof',
                'etudiant' => 'dashboard_student'
            ];
            $redirect_page = $dashboard_map[$_SESSION['user_role']] ?? 'login';
            header('Location: ' . APP_URL . '/index.php?page=' . $redirect_page);
            exit;
        }
    }
}
