<?php
error_log("DEBUG: views/professor/dashboard_prof.php is being executed.");
// This file is now protected by the router in public/index.php
?>

<div class="dashboard-container">
    <h2>Tableau de Bord Professeur</h2>
    <p>Bienvenue, <?php echo htmlspecialchars($_SESSION['user_name']); ?> !</p>
    
    <div class="dashboard-grid">
        <div class="dashboard-card">
            <h3>Mes Matières</h3>
            <p>Consulter les matières qui vous sont affectées pour la période en cours.</p>
            <a href="?page=prof_subjects" class="btn-card">Voir mes Matières</a>
        </div>
        <div class="dashboard-card">
            <h3>Saisie des Notes</h3>
            <p>Saisir ou importer les notes de vos étudiants.</p>
            <a href="?page=prof_subjects" class="btn-card">Commencer la Saisie</a>
        </div>
        <div class="dashboard-card">
            <h3>Validation de la Saisie</h3>
            <p>Vérifier et valider vos saisies pour les verrouiller.</p>
            <a href="?page=prof_validation" class="btn-card">Valider mes Saisies</a>
        </div>
        <div class="dashboard-card">
            <h3>Statistiques</h3>
            <p>Visualiser les statistiques de vos matières.</p>
            <a href="?page=prof_statistics" class="btn-card">Voir les Statistiques</a>
        </div>
    </div>
</div>


