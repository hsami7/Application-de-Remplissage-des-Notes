<?php
// This file is now protected by the router in public/index.php
require_once __DIR__ . '/../../core/actions/student_actions.php';

$student_id = $_SESSION['user_id'];
$periods = get_published_periods_for_student($student_id);
?>

<div class="container">
    <h2>Télécharger un Relevé de Notes</h2>
    <p>Sélectionnez la période pour laquelle vous souhaitez télécharger le relevé de notes.</p>

    <?php if (empty($periods)): ?>
        <div class="alert alert-info">
            Aucun relevé de notes n'est actuellement disponible au téléchargement.
        </div>
    <?php else: ?>
        <div class="list-group">
            <?php foreach ($periods as $period): ?>
                <a href="?action=generate_transcript&periode_id=<?php echo $period['id']; ?>" class="list-group-item list-group-item-action">
                    <?php echo htmlspecialchars($period['nom']); ?>
                </a>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <a href="?page=dashboard_student" class="btn btn-secondary mt-3">Retour au Tableau de Bord</a>
</div>