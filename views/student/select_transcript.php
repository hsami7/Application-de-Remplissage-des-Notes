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
        <table class="table" style="width: 100%; margin-top: 1.5rem;">
            <thead>
                <tr style="background-color: #f2f2f2;">
                    <th style="padding: 12px;">Période</th>
                    <th style="padding: 12px; text-align: center; width: 150px;">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($periods as $period): ?>
                    <tr>
                        <td style="padding: 12px; border-bottom: 1px solid #ddd;"><?php echo htmlspecialchars($period['nom']); ?></td>
                        <td style="padding: 12px; border-bottom: 1px solid #ddd; text-align: center;">
                            <a href="?action=generate_transcript&periode_id=<?php echo $period['id']; ?>" class="btn">
                                Télécharger
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

    <a href="?page=dashboard_student" class="btn btn-secondary mt-3">Retour au Tableau de Bord</a>
</div>