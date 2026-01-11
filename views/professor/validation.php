<?php
$pdo = getDBConnection();
$prof_id = $_SESSION['user_id'];

// Fetch validation status for all subjects assigned to the professor
$stmt = $pdo->prepare("
    SELECT 
        m.id as matiere_id,
        m.nom as matiere_nom, 
        p.id as periode_id,
        p.nom as periode_nom,
        ps.valide_par_prof,
        ps.date_validation,
        (SELECT COUNT(DISTINCT im.etudiant_id) FROM inscriptions_matieres im WHERE im.matiere_id = m.id AND im.periode_id = p.id) as total_etudiants,
        (SELECT COUNT(DISTINCT n.etudiant_id) FROM notes n JOIN configuration_colonnes cc ON n.colonne_id = cc.id WHERE cc.matiere_id = m.id AND cc.periode_id = p.id) as etudiants_avec_notes
    FROM affectations_profs ap
    JOIN matieres m ON ap.matiere_id = m.id
    JOIN periodes p ON ap.periode_id = p.id
    LEFT JOIN progression_saisie ps ON ps.matiere_id = m.id AND ps.periode_id = p.id AND ps.professeur_id = ap.professeur_id
    WHERE ap.professeur_id = ?
    ORDER BY p.nom, m.nom
");
$stmt->execute([$prof_id]);
$subjects_status = $stmt->fetchAll();

?>

<div class="page-container">
    <h2>Validation des Saisies</h2>
    <p>Consultez l'état d'avancement de la saisie de vos notes et validez vos matières lorsque la saisie est complète.</p>

    <div class="list-container">
        <?php
        // Display feedback messages
        if (isset($_SESSION['success_message'])) {
            echo '<div class="message success">' . htmlspecialchars($_SESSION['success_message']) . '</div>';
            unset($_SESSION['success_message']);
        }
        if (isset($_SESSION['error_message'])) {
            echo '<div class="message error">' . htmlspecialchars($_SESSION['error_message']) . '</div>';
            unset($_SESSION['error_message']);
        }
        ?>

        <?php if (empty($subjects_status)): ?>
            <div class="message info">
                Aucune matière ne vous est affectée.
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>Matière</th>
                            <th>Période</th>
                            <th>Progression</th>
                            <th>Statut</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($subjects_status as $status): ?>
                            <?php
                                $progress = ($status['total_etudiants'] > 0) ? ($status['etudiants_avec_notes'] / $status['total_etudiants']) * 100 : 0;
                            ?>
                            <tr>
                                <td><?php echo htmlspecialchars($status['matiere_nom']); ?></td>
                                <td><?php echo htmlspecialchars($status['periode_nom']); ?></td>
                                <td>
                                    <div style="width: 100%; background-color: #e0e0e0; border-radius: 4px;">
                                        <div style="width: <?php echo $progress; ?>%; background-color: <?php echo ($progress == 100) ? 'var(--success-color)' : 'var(--accent-color)'; ?>; color: white; text-align: center; border-radius: 4px; padding: 2px 0;">
                                            <?php echo round($progress); ?>%
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <?php if ($status['valide_par_prof']): ?>
                                        <span class="status-publiee" style="background-color: var(--success-color);">Validé le <?php echo date('d/m/Y', strtotime($status['date_validation'])); ?></span>
                                    <?php else: ?>
                                        <span class="status-a_venir">En attente</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($status['valide_par_prof']): ?>
                                        <span style="color: var(--medium-text-color);">Verrouillé</span>
                                    <?php else: ?>
                                        <form method="POST" action="<?php echo APP_URL; ?>/index.php?action=validate_grades" onsubmit="return confirm('Êtes-vous sûr de vouloir valider ? Cette action est irréversible.');">
                                            <input type="hidden" name="periode_id" value="<?php echo $status['periode_id']; ?>">
                                            <input type="hidden" name="matiere_id" value="<?php echo $status['matiere_id']; ?>">
                                            <button type="submit" class="action-btn edit" <?php if ($progress < 100) echo 'disabled'; ?>>
                                                Valider
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <div style="margin-top: 1rem; font-size: 0.9rem; color: var(--medium-text-color);">
                * La validation n'est possible que lorsque la progression de la saisie atteint 100%.
            </div>
        <?php endif; ?>
    </div>
</div>
