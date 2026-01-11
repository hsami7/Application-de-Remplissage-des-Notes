<?php
$pdo = getDBConnection();
$prof_id = $_SESSION['user_id'];

// This page now requires a subject and period to be selected.
$selected_matiere_id = isset($_GET['matiere_id']) ? (int)$_GET['matiere_id'] : null;
$selected_periode_id = isset($_GET['periode_id']) ? (int)$_GET['periode_id'] : null;

if (!$selected_matiere_id || !$selected_periode_id) {
    // If no subject is selected, show an error and link back to the subject list.
    ?>
    <div class="page-container">
        <h2>Erreur</h2>
        <div class="message error">
            Aucune matière n'a été sélectionnée. Veuillez choisir une matière dans la liste.
        </div>
        <a href="?page=prof_subjects" class="btn btn-back">Retour à la liste des matières</a>
    </div>
    <?php
    exit; // Stop execution
}

// --- Récupérer les données pour la grille de saisie ---

// 1. Vérifier les droits du professeur
$stmt_auth = $pdo->prepare("SELECT id FROM affectations_profs WHERE professeur_id = ? AND matiere_id = ? AND periode_id = ?");
$stmt_auth->execute([$prof_id, $selected_matiere_id, $selected_periode_id]);
if ($stmt_auth->rowCount() === 0) {
    ?>
    <div class="page-container">
        <h2>Accès non autorisé</h2>
        <div class="message error">
            Vous n'êtes pas autorisé à accéder aux notes de cette matière.
        </div>
        <a href="?page=prof_subjects" class="btn btn-back">Retour à la liste des matières</a>
    </div>
    <?php
    exit;
}


// 2. Infos matière/période
$stmt = $pdo->prepare("SELECT m.nom as matiere_nom, p.nom as periode_nom FROM matieres m, periodes p WHERE m.id = ? AND p.id = ?");
$stmt->execute([$selected_matiere_id, $selected_periode_id]);
$context = $stmt->fetch();

// 3. Colonnes configurées
$stmt_cols = $pdo->prepare("SELECT id, nom_colonne, note_max FROM configuration_colonnes WHERE matiere_id = ? AND periode_id = ? ORDER BY ordre ASC");
$stmt_cols->execute([$selected_matiere_id, $selected_periode_id]);
$colonnes = $stmt_cols->fetchAll();

// 4. Étudiants inscrits
$stmt_etudiants = $pdo->prepare("
    SELECT u.id, u.nom, u.prenom 
    FROM inscriptions_matieres im
    JOIN utilisateurs u ON im.etudiant_id = u.id
    WHERE im.matiere_id = ? AND im.periode_id = ?
    ORDER BY u.nom, u.prenom
");
$stmt_etudiants->execute([$selected_matiere_id, $selected_periode_id]);
$etudiants = $stmt_etudiants->fetchAll();

// 5. Notes existantes
$stmt_notes = $pdo->prepare("
    SELECT etudiant_id, colonne_id, valeur, statut 
    FROM notes 
    WHERE etudiant_id IN (SELECT etudiant_id FROM inscriptions_matieres WHERE matiere_id = ? AND periode_id = ?)
    AND colonne_id IN (SELECT id FROM configuration_colonnes WHERE matiere_id = ? AND periode_id = ?)
");
$stmt_notes->execute([$selected_matiere_id, $selected_periode_id, $selected_matiere_id, $selected_periode_id]);
$notes_raw = $stmt_notes->fetchAll();
$notes = [];
foreach ($notes_raw as $note) {
    $notes[$note['etudiant_id']][$note['colonne_id']] = [
        'valeur' => $note['valeur'],
        'statut' => $note['statut']
    ];
}

// 6. Statut de validation
$stmt_validation = $pdo->prepare("SELECT valide_par_prof FROM progression_saisie WHERE matiere_id = ? AND periode_id = ? AND professeur_id = ?");
$stmt_validation->execute([$selected_matiere_id, $selected_periode_id, $prof_id]);
$is_validated = $stmt_validation->fetchColumn();

?>
<div class="page-container" style="max-width: 95%;">
    <a href="?page=prof_subjects" class="btn btn-back" style="margin-bottom: 1rem;">Retour à la liste des matières</a>
    <h2>Saisie des notes pour "<?php echo htmlspecialchars($context['matiere_nom']); ?>"</h2>
    <p>Période: <?php echo htmlspecialchars($context['periode_nom']); ?></p>

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
        <?php if ($is_validated): ?>
            <div class="message success">
                Vous avez déjà validé la saisie pour cette matière. Les notes sont verrouillées.
            </div>
        <?php endif; ?>

        <?php if (empty($etudiants)): ?>
            <div class="message info">
                Il n'y a aucun étudiant inscrit pour cette matière.
            </div>
        <?php elseif (empty($colonnes)): ?>
            <div class="message error">
                Aucune colonne de note n'a été configurée pour cette matière. Veuillez contacter l'administrateur.
            </div>
        <?php else: ?>
            <form method="POST" action="<?php echo APP_URL; ?>/index.php?action=save_grades">
        
                <input type="hidden" name="periode_id" value="<?php echo $selected_periode_id; ?>">
                <input type="hidden" name="matiere_id" value="<?php echo $selected_matiere_id; ?>">

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
                                                   value="<?php echo htmlspecialchars($display_value); ?>"
                                                   <?php if ($is_validated) echo 'disabled'; ?>>
                                        </td>
                                    <?php endforeach; ?>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php if (!$is_validated): ?>
                    <div style="display: flex; justify-content: flex-end; align-items: center; gap: 1rem; margin-top: 1.5rem;">
                        <button type="submit" class="btn">Enregistrer les Modifications</button>
                        <a href="?page=prof_validation" class="btn btn-secondary">Valider la Saisie</a>
                    </div>
                <?php endif; ?>
            </form>
        <?php endif; ?>
    </div>
</div>
