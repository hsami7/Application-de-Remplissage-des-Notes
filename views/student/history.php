<?php
// This file is now protected by the router in public/index.php
require_once __DIR__ . '/../../core/actions/student_actions.php';

// Fetch the student's historical grades
$student_id = $_SESSION['user_id'];
$historical_grades = get_student_history($student_id);

?>

<div class="container">
    <h2>Historique de vos Notes</h2>
    <p>Retrouvez ici les notes et moyennes de vos périodes précédentes.</p>

    <?php if (empty($historical_grades)): ?>
        <div class="alert alert-info">
            Vous n'avez pas d'historique de notes pour le moment.
        </div>
    <?php else: ?>
        <?php foreach ($historical_grades as $data): ?>
            <div class="card mb-4">
                <div class="card-header">
                    <h3><?php echo htmlspecialchars($data['period']); ?></h3>
                </div>
                <div class="card-body">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Matière</th>
                                <th>Moyenne</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($data['subjects'] as $subject): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($subject['name']); ?></td>
                                    <td><?php echo htmlspecialchars(number_format($subject['grade'], 2)); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

    <a href="?page=dashboard_student" class="btn btn-secondary">Retour au Tableau de Bord</a>
</div>
