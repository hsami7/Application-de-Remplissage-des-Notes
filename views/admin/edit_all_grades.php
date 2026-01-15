<?php
// views/admin/edit_all_grades.php
authorize_user(['admin']);

$pdo = getDBConnection();

$matiere_id = filter_input(INPUT_GET, 'matiere_id', FILTER_VALIDATE_INT);
$periode_id = filter_input(INPUT_GET, 'periode_id', FILTER_VALIDATE_INT);

if (!$matiere_id || !$periode_id) {
    $_SESSION['error_message'] = "Matière ou période non spécifiée.";
    header('Location: ' . APP_URL . '/index.php?page=view_grades_admin');
    exit;
}

// --- Data Fetching (same as view_grades_admin) ---

// 1. Infos matière/période
$stmt = $pdo->prepare("SELECT m.nom as matiere_nom, f.nom as filiere_nom, p.nom as periode_nom FROM matieres m JOIN periodes p ON p.id = :periode_id JOIN filieres f ON m.filiere_id = f.id WHERE m.id = :matiere_id");
$stmt->execute([':periode_id' => $periode_id, ':matiere_id' => $matiere_id]);
$context = $stmt->fetch();

// 2. Colonnes configurées
$stmt_cols = $pdo->prepare("SELECT id, nom_colonne, note_max FROM configuration_colonnes WHERE matiere_id = ? AND periode_id = ? ORDER BY ordre ASC");
$stmt_cols->execute([$matiere_id, $periode_id]);
$colonnes = $stmt_cols->fetchAll();

// 3. Étudiants inscrits
$stmt_etudiants = $pdo->prepare("
    SELECT u.id, u.nom, u.prenom 
    FROM inscriptions_matieres im
    JOIN utilisateurs u ON im.etudiant_id = u.id
    WHERE im.matiere_id = ? AND im.periode_id = ?
    ORDER BY u.nom, u.prenom
");
$stmt_etudiants->execute([$matiere_id, $periode_id]);
$etudiants = $stmt_etudiants->fetchAll();

// 4. Notes existantes
$stmt_notes = $pdo->prepare("
    SELECT etudiant_id, colonne_id, valeur, statut 
    FROM notes 
    WHERE etudiant_id IN (SELECT etudiant_id FROM inscriptions_matieres WHERE matiere_id = ? AND periode_id = ?)
    AND colonne_id IN (SELECT id FROM configuration_colonnes WHERE matiere_id = ? AND periode_id = ?)
");
$stmt_notes->execute([$matiere_id, $periode_id, $matiere_id, $periode_id]);
$notes_raw = $stmt_notes->fetchAll();
$notes = [];
foreach ($notes_raw as $note) {
    $notes[$note['etudiant_id']][$note['colonne_id']] = [
        'valeur' => $note['valeur'],
        'statut' => $note['statut']
    ];
}
?>

<div class="page-container" style="max-width: 95%;">
    <a href="?page=view_grades_admin&matiere_id=<?php echo $matiere_id; ?>&periode_id=<?php echo $periode_id; ?>" class="btn btn-back" style="margin-bottom: 1rem;">Annuler et Retourner à la Vue</a>
    <h2>Modification des notes pour "<?php echo htmlspecialchars($context['matiere_nom'] . ' (' . $context['filiere_nom'] . ')'); ?>"</h2>
    <p>Période: <?php echo htmlspecialchars($context['periode_nom']); ?></p>

    <div class="list-container">
        <?php if (empty($etudiants) || empty($colonnes)): ?>
             <div class="message info">
                Il n'y a aucun étudiant ou aucune colonne configurée pour cette matière.
            </div>
        <?php else: ?>
            <form method="POST" action="<?php echo APP_URL; ?>/index.php?action=update_all_grades_admin">
                <input type="hidden" name="periode_id" value="<?php echo $periode_id; ?>">
                <input type="hidden" name="matiere_id" value="<?php echo $matiere_id; ?>">
                <input type="hidden" name="return_url" value="<?php echo urlencode(APP_URL . '/index.php?page=view_grades_admin&matiere_id=' . $matiere_id . '&periode_id=' . $periode_id); ?>">
                
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>Étudiant</th>
                                <?php foreach ($colonnes as $col): ?>
                                    <th title="Note max: <?php echo $col['note_max']; ?>">
                                        <?php echo htmlspecialchars($col['nom_colonne']); ?>
                                    </th>
                                <?php endforeach; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($etudiants as $etudiant): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($etudiant['prenom'] . ' ' . $etudiant['nom']); ?></td>
                                    <?php foreach ($colonnes as $col): ?>
                                        <?php
                                        $current_note = $notes[$etudiant['id']][$col['id']] ?? null;
                                        $display_value = '';
                                        if ($current_note) {
                                            if ($current_note['statut'] !== 'saisie') {
                                                $display_value = strtoupper($current_note['statut']);
                                            } else if (isset($current_note['valeur'])) {
                                                $display_value = rtrim(rtrim(number_format($current_note['valeur'], 2, ',', ''), '0'), ',');
                                            }
                                        }
                                        ?>
                                        <td>
                                            <input type="text" 
                                                   name="grades[<?php echo $etudiant['id']; ?>][<?php echo $col['id']; ?>]"
                                                   class="grade-input"
                                                   value="<?php echo htmlspecialchars($display_value); ?>">
                                        </td>
                                    <?php endforeach; ?>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <div style="display: flex; justify-content: flex-end; margin-top: 1.5rem;">
                    <button type="submit" class="btn">Enregistrer les Modifications</button>
                </div>
            </form>
        <?php endif; ?>
    </div>
</div>
