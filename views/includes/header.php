<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo APP_NAME; ?></title>
    <!-- Lien vers la feuille de style principale -->
    <link rel="stylesheet" href="<?php echo APP_URL; ?>/css/style.css">
    <!-- Vous pouvez ajouter d'autres liens CSS ou polices ici -->
</head>
<body>

<div class="main-container">
    <header>
        <h1>
            <?php if (isset($_SESSION['user_id'])): ?>
                <?php
                // Determine the dashboard page based on user role
                $role_dashboard_map = [
                    'admin' => 'dashboard_admin',
                    'professeur' => 'dashboard_prof',
                    'etudiant' => 'dashboard_student'
                ];
                $dashboard_page = $role_dashboard_map[$_SESSION['user_role']] ?? 'login';
                ?>
                <a href="?page=<?php echo $dashboard_page; ?>"><?php echo APP_NAME; ?></a>
            <?php else: ?>
                <a href="<?php echo APP_URL; ?>"><?php echo APP_NAME; ?></a>
            <?php endif; ?>
        </h1>
        <!-- Navigation principale (sera développée plus tard) -->
        <nav>
            <?php if (isset($_SESSION['user_id'])): 
                // Map roles to their specific dashboard page key
                $role_dashboard_map = [
                    'admin' => 'dashboard_admin',
                    'professeur' => 'dashboard_prof',
                    'etudiant' => 'dashboard_student'
                ];
                $dashboard_page = $role_dashboard_map[$_SESSION['user_role']] ?? 'login';
            ?>
                <a href="?page=<?php echo $dashboard_page; ?>">Tableau de bord</a>
                <a href="?page=change_password">Changer le mot de passe</a>
                <form action="?action=logout" method="POST" style="display: inline;">

                    <button type="submit" style="background: none; border: none; color: inherit; font: inherit; cursor: pointer; padding: 0; margin-left: 1.5rem;">Déconnexion</button>
                </form>
            <?php else: ?>
                <a href="?page=login">Connexion</a>
            <?php endif; ?>
        </nav>
    </header>
    
    <main class="content">
        <!-- Le contenu de la page sera injecté ici -->
