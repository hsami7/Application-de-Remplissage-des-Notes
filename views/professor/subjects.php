<?php
$pdo = getDBConnection();
$prof_id = $_SESSION['user_id'];

// Fetch all subjects assigned to the professor for currently open periods
$stmt = $pdo->prepare("
    SELECT 
        m.id as matiere_id, 
        m.nom as matiere_nom, 
        p.id as periode_id, 
        p.nom as periode_nom,
        (SELECT COUNT(*) FROM inscriptions_matieres WHERE matiere_id = m.id AND periode_id = p.id) as student_count
    FROM affectations_profs ap
    JOIN matieres m ON ap.matiere_id = m.id
    JOIN periodes p ON ap.periode_id = p.id
    WHERE ap.professeur_id = ? AND p.statut = 'ouverte'
    ORDER BY p.nom, m.nom
");
$stmt->execute([$prof_id]);
$assigned_subjects = $stmt->fetchAll();
?>

<div class="page-container">
    <h2>Mes Matières</h2>
    <p>Sélectionnez une matière pour saisir ou consulter les notes. Seules les matières des périodes de saisie actuellement ouvertes sont affichées.</p>

    <div class="list-container">
        <?php if (empty($assigned_subjects)): ?>
            <div class="message info">
                Aucune matière ne vous est affectée pour une période de saisie actuellement ouverte.
            </div>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>Matière</th>
                        <th>Période</th>
                        <th>Nombre d'étudiants</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($assigned_subjects as $subject): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($subject['matiere_nom']); ?></td>
                            <td><?php echo htmlspecialchars($subject['periode_nom']); ?></td>
                            <td><?php echo $subject['student_count']; ?></td>
                            <td>
                                <a href="?page=enter_grades&periode_id=<?php echo $subject['periode_id']; ?>&matiere_id=<?php echo $subject['matiere_id']; ?>" class="action-btn edit">
                                    Saisir les Notes
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>
