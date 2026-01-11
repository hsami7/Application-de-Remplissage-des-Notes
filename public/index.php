<?php
// Initialisation globale de l'application (session, config, DB, fonctions)
require_once '../core/init.php';

// --- ROUTEUR PRINCIPAL ---

$action = $_GET['action'] ?? null;
$page = $_GET['page'] ?? 'login';

// Gérer les actions (soumissions de formulaire, etc.)
if ($action) {

    
    // Define a map for actions and their corresponding files
    $action_map = [
        // Auth Actions
        'login' => '../core/actions/auth_actions.php',
        'logout' => '../core/logout.php',
        'change_password' => '../core/actions/auth_actions.php',

        // Admin Actions
        'add_period' => '../core/actions/admin_actions.php',
        'update_period' => '../core/actions/admin_actions.php',
        'delete_period' => '../core/actions/admin_actions.php',
        'change_period_status' => '../core/actions/admin_actions.php',
        'add_filiere' => '../core/actions/admin_actions.php',
        'update_filiere' => '../core/actions/admin_actions.php', // Added
        'delete_filiere' => '../core/actions/admin_actions.php', // Added
        'add_subject' => '../core/actions/admin_actions.php',
        'update_subject' => '../core/actions/admin_actions.php',
        'delete_subject' => '../core/actions/admin_actions.php',
        'add_column' => '../core/actions/admin_actions.php',
        'update_column' => '../core/actions/admin_actions.php',
        'delete_column' => '../core/actions/admin_actions.php',
        'save_formula' => '../core/actions/admin_actions.php',
        'add_assignment' => '../core/actions/admin_actions.php',
        'add_enrollment' => '../core/actions/admin_actions.php',
        'add_user' => '../core/actions/admin_actions.php',
        'update_user' => '../core/actions/admin_actions.php',
        'delete_user' => '../core/actions/admin_actions.php',
        'calculate_averages' => '../core/actions/admin_actions.php',

        // Professor Actions
        'save_grades' => '../core/actions/prof_actions.php',
        'validate_grades' => '../core/actions/prof_actions.php',

        // Student Actions
        'generate_transcript' => '../core/actions/student_actions.php',
    ];

    // Gérer les actions (soumissions de formulaire, etc.)
    if ($action && isset($action_map[$action])) {
        require_once $action_map[$action];
        // Exécuter l'action demandée
        switch ($action) {
            // Auth
            case 'login': handle_login(); break;
            case 'logout': handle_logout(); break;
            case 'change_password': handle_change_password(); break;

            // Admin
            case 'add_period': handle_add_period(); break;
            case 'update_period': handle_update_period(); break;
            case 'delete_period': handle_delete_period(); break;
            case 'change_period_status': handle_change_period_status(); break;
            case 'add_filiere': handle_add_filiere(); break;
            case 'update_filiere': handle_update_filiere(); break; // Added
            case 'delete_filiere': handle_delete_filiere(); break; // Added
            case 'add_subject': handle_add_subject(); break;
            case 'update_subject': handle_update_subject(); break;
            case 'delete_subject': handle_delete_subject(); break;
            case 'add_column': handle_add_column(); break;
            case 'update_column': handle_update_column(); break;
            case 'delete_column': handle_delete_column(); break;
            case 'save_formula': handle_save_formula(); break;
            case 'add_assignment': handle_add_assignment(); break;
            case 'add_enrollment': handle_add_enrollment(); break;
            case 'add_user': handle_add_user(); break;
            case 'update_user': handle_update_user(); break;
            case 'delete_user': handle_delete_user(); break;
            case 'calculate_averages': handle_calculate_averages(); break;

            // Professor
            case 'save_grades': handle_save_grades(); break;
            case 'validate_grades': handle_validate_grades(); break;
            
            // Student
            case 'generate_transcript': handle_generate_transcript(); break;

            default:
                // This default case should technically not be reached if $action is in $action_map
                $_SESSION['error_message'] = "Action non valide.";
                header('Location: ' . APP_URL);
                exit;
        }
    } else if ($action) { // Action is set but not in map
        $_SESSION['error_message'] = "Action non reconnue.";
        header('Location: ' . APP_URL);
        exit;
    }
} else {
    // Si aucune action n'est spécifiée, afficher une page
    
    // Mapper les pages aux rôles et aux fichiers de vue
    $page_map = [
        'login' => ['file' => 'auth/login.php', 'role' => null],
        'change_password' => ['file' => 'auth/change_password.php', 'role' => ['admin', 'professeur', 'etudiant']],

        // Admin Pages
        'dashboard_admin' => ['file' => 'admin/dashboard_admin.php', 'role' => ['admin']],
        'manage_periods' => ['file' => 'admin/manage_periods.php', 'role' => ['admin']],
        'edit_period' => ['file' => 'admin/edit_period.php', 'role' => ['admin']],
        'manage_filieres' => ['file' => 'admin/manage_filieres.php', 'role' => ['admin']],
        'edit_filiere' => ['file' => 'admin/edit_filiere.php', 'role' => ['admin']], // Added
        'manage_subjects' => ['file' => 'admin/manage_subjects.php', 'role' => ['admin']],
        'edit_subject' => ['file' => 'admin/edit_subject.php', 'role' => ['admin']],
        'configure_notes' => ['file' => 'admin/configure_notes.php', 'role' => ['admin']],
        'edit_column' => ['file' => 'admin/edit_column.php', 'role' => ['admin']],
        'manage_formulas' => ['file' => 'admin/manage_formulas.php', 'role' => ['admin']],
        'manage_assignments' => ['file' => 'admin/manage_assignments.php', 'role' => ['admin']],
        'manage_enrollments' => ['file' => 'admin/manage_enrollments.php', 'role' => ['admin']],
        'manage_users' => ['file' => 'admin/manage_users.php', 'role' => ['admin']],
        'edit_user' => ['file' => 'admin/edit_user.php', 'role' => ['admin']],
        'view_progress' => ['file' => 'admin/view_progress.php', 'role' => ['admin']], // Added


        // Professor Pages
        'dashboard_prof' => ['file' => 'professor/dashboard_prof.php', 'role' => ['professeur']],
        'prof_subjects' => ['file' => 'professor/subjects.php', 'role' => ['professeur']],
        'enter_grades' => ['file' => 'professor/enter_grades.php', 'role' => ['professeur']],
        'prof_validation' => ['file' => 'professor/validation.php', 'role' => ['professeur']],
        'prof_statistics' => ['file' => 'professor/statistics.php', 'role' => ['professeur']],

        // Student Pages
        'dashboard_student' => ['file' => 'student/dashboard_student.php', 'role' => ['etudiant']],
        'view_grades' => ['file' => 'student/view_grades.php', 'role' => ['etudiant']],
        'history' => ['file' => 'student/history.php', 'role' => ['etudiant']],
        'select_transcript' => ['file' => 'student/select_transcript.php', 'role' => ['etudiant']],
    ];

    $view_file = '../views/errors/404.php'; // Page par défaut si non trouvée

    if (isset($page_map[$page])) {
        $config = $page_map[$page];
        $path_to_view = '../views/' . $config['file'];
        
        // Gérer l'autorisation
        if ($config['role'] !== null) {
            // Si le rôle n'est pas null, on vérifie l'authentification et le rôle
            authorize_user($config['role']);
        } elseif ($page !== 'login') {
            // Pour les pages comme 'change_password' qui n'ont pas de rôle spécifique mais nécessitent une connexion
            authorize_user(); 
        }
        
        if (file_exists($path_to_view)) {
            $view_file = $path_to_view;
        } else {
            // error_log("DEBUG: File not found in router: " . $path_to_view); // Removed debugging log
            // error_log("DEBUG: Current working directory in router: " . getcwd()); // Removed debugging log
        }
    }
    
    // Inclure le layout
    // error_log("DEBUG: Attempting to include view file: " . $view_file); // Removed debugging log
    include_once '../views/includes/header.php';
    include_once $view_file;
    include_once '../views/includes/footer.php';
}