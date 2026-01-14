<?php
// This file is now protected by the router in public/index.php
?>

<div class="dashboard-container">
    <h2>Tableau de Bord Administrateur</h2>
    <p>Bienvenue, <?php echo htmlspecialchars($_SESSION['user_name']); ?> !</p>
    
    <div class="dashboard-grid">
        <div class="dashboard-card">
            <h3>Gestion des Périodes</h3>
            <p>Créer, ouvrir et fermer les périodes de notation.</p>
            <a href="?page=manage_periods" class="btn-card">Gérer les Périodes</a>
        </div>
        <div class="dashboard-card">
            <h3>Gestion des Filières</h3>
            <p>Définir les filières, niveaux et responsables.</p>
            <a href="?page=manage_filieres" class="btn-card">Gérer les Filières</a>
        </div>
        <div class="dashboard-card">
            <h3>Gestion des Matières</h3>
            <p>Configurer les matières et leurs coefficients.</p>
            <a href="?page=manage_subjects" class="btn-card">Gérer les Matières</a>
        </div>
        <div class="dashboard-card">
            <h3>Configuration des Notes</h3>
            <p>Définir les colonnes de notes pour chaque matière.</p>
            <a href="?page=configure_notes" class="btn-card">Configurer les Colonnes</a>
        </div>
        <div class="dashboard-card">
            <h3>Gestion des Formules</h3>
            <p>Définir les formules de calcul des moyennes.</p>
            <a href="?page=manage_formulas" class="btn-card">Gérer les Formules</a>
        </div>
        <div class="dashboard-card">
            <h3>Affectations</h3>
            <p>Affecter les professeurs aux matières.</p>
            <a href="?page=manage_assignments" class="btn-card">Gérer les Affectations</a>
        </div>
        <div class="dashboard-card">
            <h3>Inscriptions</h3>
            <p>Inscrire les étudiants aux matières.</p>
            <a href="?page=manage_enrollments" class="btn-card">Gérer les Inscriptions</a>
        </div>
        <div class="dashboard-card">
            <h3>Suivi de la Saisie</h3>
            <p>Voir la progression de la saisie des notes.</p>
            <a href="?page=view_progress" class="btn-card">Voir la Progression</a>
        </div>
        <div class="dashboard-card">
            <h3>Consultation des Notes</h3>
            <p>Consulter les notes saisies pour une matière.</p>
            <a href="?page=view_grades_admin" class="btn-card">Consulter les Notes</a>
        </div>
        <div class="dashboard-card">
            <h3>Utilisateurs</h3>
            <p>Gérer les comptes utilisateurs (profs, étudiants).</p>
            <a href="?page=manage_users" class="btn-card">Gérer les Utilisateurs</a>
        </div>
    </div>
</div>


