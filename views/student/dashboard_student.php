<?php
// This file is now protected by the router in public/index.php
?>

<div class="dashboard-container">
    <h2>Tableau de Bord Étudiant</h2>
    <p>Bienvenue, <?php echo htmlspecialchars($_SESSION['user_name']); ?> !</p>
    
    <div class="dashboard-grid">
        <div class="dashboard-card">
            <h3>Mes Notes</h3>
            <p>Consulter vos notes pour la période en cours une fois qu'elles sont publiées.</p>
            <a href="?page=view_grades" class="btn-card">Voir mes Notes</a>
        </div>
        <div class="dashboard-card">
            <h3>Relevé de Notes</h3>
            <p>Télécharger votre relevé de notes officiel au format PDF.</p>
            <a href="?page=select_transcript" class="btn-card">Télécharger mon Relevé</a>
        </div>
        <div class="dashboard-card">
            <h3>Historique</h3>
            <p>Accéder aux archives de vos notes des semestres précédents.</p>
            <a href="?page=history" class="btn-card">Voir l'Historique</a>
        </div>
    </div>
</div>


