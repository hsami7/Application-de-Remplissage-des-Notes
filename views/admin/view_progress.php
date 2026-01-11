<?php
// views/admin/view_progress.php
authorize_user(['admin']);

$pdo = getDBConnection();

// Fetch grade entry progress data
$stmt = $pdo->query("
    SELECT 
        ps.id,
        m.nom as matiere_nom,
        p.nom as periode_nom,
        CONCAT(u.prenom, ' ', u.nom) as prof_nom,
        ps.total_etudiants,
        ps.total_notes_attendues,
        ps.notes_saisies,
        ps.pourcentage,
        ps.valide_par_prof,
        ps.date_mise_a_jour
    FROM progression_saisie ps
    JOIN matieres m ON ps.matiere_id = m.id
    JOIN periodes p ON ps.periode_id = p.id
    JOIN utilisateurs u ON ps.professeur_id = u.id
    ORDER BY p.date_debut_saisie DESC, m.nom ASC
");
$progress_data = $stmt->fetchAll();

?>

<div class="page-container">
    <h2>Suivi de la Saisie des Notes</h2>

    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="message success">
            <?php echo htmlspecialchars($_SESSION['success_message']); unset($_SESSION['success_message']); ?>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error_message'])): ?>
        <div class="message error">
            <?php echo htmlspecialchars($_SESSION['error_message']); unset($_SESSION['error_message']); ?>
        </div>
    <?php endif; ?>

    <div class="list-container">
        <h3>Progression de la Saisie par Matière/Période</h3>
        <?php if (empty($progress_data)): ?>
            <p>Aucune donnée de progression de saisie disponible pour le moment.</p>
        <?php else: ?>
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>Matière</th>
                            <th>Période</th>
                            <th>Professeur</th>
                            <th>Étudiants</th>
                            <th>Notes Attendues</th>
                            <th>Notes Saisies</th>
                            <th>Pourcentage</th>
                            <th>Validé par Prof</th>
                            <th>Dernière Maj.</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($progress_data as $progress): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($progress['matiere_nom']); ?></td>
                                <td><?php echo htmlspecialchars($progress['periode_nom']); ?></td>
                                <td><?php echo htmlspecialchars($progress['prof_nom']); ?></td>
                                <td><?php echo htmlspecialchars($progress['total_etudiants']); ?></td>
                                <td><?php echo htmlspecialchars($progress['total_notes_attendues']); ?></td>
                                <td><?php echo htmlspecialchars($progress['notes_saisies']); ?></td>
                                <td>
                                    <div style="width: 80px; background-color: #e0e0e0; border-radius: 5px; overflow: hidden;">
                                        <div style="width: <?php echo htmlspecialchars($progress['pourcentage']); ?>%; background-color: var(--primary-color); color: white; text-align: center; border-radius: 5px; white-space: nowrap;">
                                            <?php echo htmlspecialchars(round($progress['pourcentage'])); ?>%
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <?php if ($progress['valide_par_prof']): ?>
                                        <span class="message success" style="padding: 0.3rem 0.6rem; font-size: 0.8rem; margin: 0; display: inline-block;">Oui</span>
                                    <?php else: ?>
                                        <span class="message info" style="padding: 0.3rem 0.6rem; font-size: 0.8rem; margin: 0; display: inline-block;">Non</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($progress['date_mise_a_jour']); ?></td>
                                <td>
                                    <!-- Add actions like "Voir Détails", "Forcer Validation" -->
                                    <button class="action-btn edit">Détails</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>
