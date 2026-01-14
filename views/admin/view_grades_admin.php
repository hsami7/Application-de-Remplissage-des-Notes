<?php
// views/admin/view_grades_admin.php
authorize_user(['admin']);

$pdo = getDBConnection();

$matiere_id = filter_input(INPUT_GET, 'matiere_id', FILTER_VALIDATE_INT);
$periode_id = filter_input(INPUT_GET, 'periode_id', FILTER_VALIDATE_INT);

// Fetch subjects and periods for the selection form
$all_matieres_stmt = $pdo->query("
    SELECT m.id, m.nom, f.nom AS filiere_nom
    FROM matieres m
    JOIN filieres f ON m.filiere_id = f.id
    ORDER BY m.nom, f.nom
");
$all_matieres = $all_matieres_stmt->fetchAll();
$all_periodes = $pdo->query("SELECT id, nom, annee_universitaire FROM periodes ORDER BY annee_universitaire DESC, nom ASC")->fetchAll();


if (!$matiere_id || !$periode_id) {
    // Display selection form if IDs are missing
    ?>
    <div class="page-container">
        <h2>Consultation des Notes</h2>
        <h3>Sélectionnez une matière et une période</h3>

        <?php if (isset($_SESSION['error_message'])):
            echo '<div class="message error">' . htmlspecialchars($_SESSION['error_message']) . '</div>';
            unset($_SESSION['error_message']);
        endif; ?>

        <div class="form-container">
            <form action="<?php echo APP_URL; ?>/index.php" method="GET">
                <input type="hidden" name="page" value="view_grades_admin">
                <div class="form-grid">
                    <div class="input-group">
                        <label for="matiere_id_select">Matière</label>
                        <select id="matiere_id_select" name="matiere_id" required>
                            <option value="">-- Sélectionner une matière --</option>
                            <?php foreach ($all_matieres as $matiere):
                                ?><option value="<?php echo $matiere['id']; ?>"><?php echo htmlspecialchars($matiere['nom']) . ' (' . htmlspecialchars($matiere['filiere_nom']) . ')'; ?></option>
                            <?php endforeach;
                            ?>
                        </select>
                    </div>
                    <div class="input-group">
                        <label for="periode_id_select">Période</label>
                        <select id="periode_id_select" name="periode_id" required>
                            <option value="">-- Sélectionner une période --</option>
                            <?php foreach ($all_periodes as $periode):
                                ?><option value="<?php echo $periode['id']; ?>"><?php echo htmlspecialchars($periode['nom']) . " (" . htmlspecialchars($periode['annee_universitaire']) . ")"; ?></option>
                            <?php endforeach;
                            ?>
                        </select>
                    </div>
                </div>
                <button type="submit" class="btn">Afficher les Notes</button>
            </form>
        </div>
    </div>
    <?php
} else {
    require_once '../core/classes/FormulaParser.php';
    $parser = new FormulaParser();

    // 1. Infos matière/période
    $stmt = $pdo->prepare("SELECT m.nom as matiere_nom, m.seuil_validation, f.nom as filiere_nom, p.nom as periode_nom FROM matieres m JOIN periodes p ON p.id = ? JOIN filieres f ON m.filiere_id = f.id WHERE m.id = ?");
    $stmt->execute([$periode_id, $matiere_id]);
    $context = $stmt->fetch();
    $seuil_validation = $context['seuil_validation'] ?? 10.0;

    if (!$context) {
        $_SESSION['error_message'] = "Matière ou période introuvable.";
        header('Location: ' . APP_URL . '/index.php?page=view_grades_admin');
        exit;
    }

    // Fetch formula
    $stmt_formule = $pdo->prepare("SELECT formule FROM formules WHERE matiere_id = ? AND periode_id = ?");
    $stmt_formule->execute([$matiere_id, $periode_id]);
    $formula = $stmt_formule->fetchColumn();

    // 2. Colonnes configurées
    $stmt_cols = $pdo->prepare("SELECT id, nom_colonne, code_colonne, note_max FROM configuration_colonnes WHERE matiere_id = ? AND periode_id = ? ORDER BY ordre ASC");
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
        <a href="?page=view_grades_admin" class="btn btn-back" style="margin-bottom: 1rem;">Retour à la sélection</a>
        <h2>Consultation des notes pour "<?php echo htmlspecialchars($context['matiere_nom'] . ' (' . $context['filiere_nom'] . ')'); ?>"</h2>
        <p>Période: <?php echo htmlspecialchars($context['periode_nom']); ?></p>

        <div class="list-container">
            <?php if (empty($etudiants)): ?>
                <div class="message info">
                    Il n'y a aucun étudiant inscrit pour cette matière.
                </div>
            <?php elseif (empty($colonnes)): ?>
                <div class="message error">
                    Aucune colonne de note n'a été configurée pour cette matière.
                </div>
            <?php else: ?>
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
                                <th>Moyenne</th>
                                <th>Décision</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $student_averages = [];
                            foreach ($etudiants as $etudiant):
                                $student_grades = [];
                                foreach ($colonnes as $col) {
                                    $note_info = $notes[$etudiant['id']][$col['id']] ?? null;
                                    $grade_value = null;
                                    if ($note_info) {
                                        if ($note_info['statut'] === 'saisie' && isset($note_info['valeur'])) {
                                            $grade_value = $note_info['valeur'];
                                        }
                                    }
                                    $student_grades[$col['code_colonne']] = $grade_value;
                                }

                                $moyenne = null;
                                if ($formula) {
                                    try {
                                        $moyenne = $parser->evaluer($formula, $student_grades);
                                        if ($moyenne !== null) {
                                            $student_averages[] = $moyenne;
                                        }
                                    } catch (Exception $e) {
                                        // error_log($e->getMessage());
                                        $moyenne = 'Erreur';
                                    }
                                }
                                
                                $decision = 'en_attente';
                                if ($moyenne !== null && $moyenne !== 'Erreur') {
                                    if ($moyenne < 7) {
                                        $decision = 'non_valide';
                                    } elseif ($moyenne >= 7 && $moyenne < $seuil_validation) {
                                        $decision = 'rattrapage';
                                    } elseif ($moyenne >= $seuil_validation) {
                                        $decision = 'valide';
                                    }
                                }
                            ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($etudiant['prenom'] . ' ' . $etudiant['nom']); ?></td>
                                    <?php foreach ($colonnes as $col): ?>
                                        <?php
                                        $current_note = $notes[$etudiant['id']][$col['id']] ?? null;
                                        $display_value = '-';
                                        if ($current_note) {
                                            if ($current_note['statut'] !== 'saisie') {
                                                $display_value = strtoupper($current_note['statut']);
                                            } else if (isset($current_note['valeur'])) {
                                                $display_value = rtrim(rtrim(number_format($current_note['valeur'], 2, ',', ''), '0'), ',');
                                            }
                                        }
                                        ?>
                                        <td>
                                            <?php echo htmlspecialchars($display_value); ?>
                                        </td>
                                    <?php endforeach; ?>
                                    <td>
                                        <?php
                                        if ($moyenne !== null && $moyenne !== 'Erreur') {
                                            echo htmlspecialchars(number_format($moyenne, 2, ',', ''));
                                        } elseif ($moyenne === 'Erreur') {
                                            echo '<span class="message error" style="padding: 0.2rem; font-size: 0.8rem;">Erreur</span>';
                                        } else {
                                            echo '-';
                                        }
                                        ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($decision); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="<?php echo count($colonnes) + 1; ?>" style="text-align: right; font-weight: bold;">Moyenne de la classe</td>
                                <td style="font-weight: bold;">
                                    <?php
                                    if (!empty($student_averages)) {
                                        $class_average = array_sum($student_averages) / count($student_averages);
                                        echo htmlspecialchars(number_format($class_average, 2, ',', ''));
                                    } else {
                                        echo '-';
                                    }
                                    ?>
                                </td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
<?php
}
?>
