<?php
// views/admin/configure_notes.php
authorize_user(['admin']);

$pdo = getDBConnection();

$matiere_id = filter_input(INPUT_GET, 'matiere_id', FILTER_VALIDATE_INT);
$periode_id = filter_input(INPUT_GET, 'periode_id', FILTER_VALIDATE_INT);

// Fetch subjects and periods for the selection form
$all_matieres = $pdo->query("SELECT id, nom FROM matieres ORDER BY nom")->fetchAll();
$all_periodes = $pdo->query("SELECT id, nom, annee_universitaire FROM periodes ORDER BY annee_universitaire DESC, nom ASC")->fetchAll();


if (!$matiere_id || !$periode_id) {
    // Display selection form if IDs are missing
    ?>
    <div class="page-container">
        <h2>Configuration des Notes</h2>
        <h3>Sélectionnez une matière et une période</h3>

        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="message error">
                <?php echo htmlspecialchars($_SESSION['error_message']); unset($_SESSION['error_message']); ?>
            </div>
        <?php endif; ?>

        <div class="form-container">
            <form action="<?php echo APP_URL; ?>/index.php" method="GET">
                <input type="hidden" name="page" value="configure_notes">
                <div class="form-grid">
                    <div class="input-group">
                        <label for="matiere_id_select">Matière</label>
                        <select id="matiere_id_select" name="matiere_id" required>
                            <option value="">-- Sélectionner une matière --</option>
                            <?php foreach ($all_matieres as $matiere): ?>
                                <option value="<?php echo $matiere['id']; ?>"><?php echo htmlspecialchars($matiere['nom']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="input-group">
                        <label for="periode_id_select">Période</label>
                        <select id="periode_id_select" name="periode_id" required>
                            <option value="">-- Sélectionner une période --</option>
                            <?php foreach ($all_periodes as $periode): ?>
                                <option value="<?php echo $periode['id']; ?>"><?php echo htmlspecialchars($periode['nom']) . " (" . htmlspecialchars($periode['annee_universitaire']) . ")"; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <button type="submit" class="btn">Afficher la Configuration</button>
            </form>
        </div>
    </div>
    <?php
} else {
    // Proceed to display configuration if IDs are present

    // Fetch subject and period info
    $stmt_context = $pdo->prepare("SELECT m.nom as matiere_nom, p.nom as periode_nom FROM matieres m JOIN periodes p ON p.id = ? WHERE m.id = ?");
    $stmt_context->execute([$periode_id, $matiere_id]);
    $context = $stmt_context->fetch();

    if (!$context) {
        $_SESSION['error_message'] = "Matière ou période introuvable.";
        header('Location: ' . APP_URL . '/index.php?page=dashboard_admin');
        exit;
    }

    // Fetch existing columns for this subject and period
    $stmt_cols = $pdo->prepare("SELECT id, nom_colonne, code_colonne, type, note_max, coefficient, obligatoire, ordre FROM configuration_colonnes WHERE matiere_id = ? AND periode_id = ? ORDER BY ordre ASC");
    $stmt_cols->execute([$matiere_id, $periode_id]);
    $colonnes = $stmt_cols->fetchAll();

    ?>

    <div class="page-container">
        <h2>Configuration des Colonnes de Notes</h2>
        <h3>Matière: <?php echo htmlspecialchars($context['matiere_nom']); ?> (Période: <?php echo htmlspecialchars($context['periode_nom']); ?>)</h3>

        <!-- Afficher les messages de session -->
        <?php
        if (isset($_SESSION['success_message'])) {
            echo '<div class="message success">' . htmlspecialchars($_SESSION['success_message']) . '</div>';
            unset($_SESSION['success_message']);
        }
        if (isset($_SESSION['error_message'])) {
            echo '<div class="message error">' . htmlspecialchars($_SESSION['error_message']) . '</div>';
            unset($_SESSION['error_message']);
        }
        ?>

        <!-- Formulaire d'ajout -->
        <div class="form-container">
            <h3>Ajouter une nouvelle Colonne</h3>
            <form action="<?php echo APP_URL; ?>/index.php?action=add_column" method="post">
                <input type="hidden" name="matiere_id" value="<?php echo htmlspecialchars($matiere_id); ?>">
                <input type="hidden" name="periode_id" value="<?php echo htmlspecialchars($periode_id); ?>">

                <div class="form-grid">
                    <div class="input-group">
                        <label for="nom_colonne">Nom de la Colonne</label>
                        <input type="text" id="nom_colonne" name="nom_colonne" required>
                    </div>
                    <div class="input-group">
                        <label for="code_colonne">Code pour formule (ex: DS1)</label>
                        <input type="text" id="code_colonne" name="code_colonne" required>
                    </div>
                    <div class="input-group">
                        <label for="type">Type</label>
                        <select id="type" name="type" required>
                            <option value="note">Note</option>
                            <option value="bonus">Bonus</option>
                            <option value="malus">Malus</option>
                            <option value="info">Info (non calculable)</option>
                        </select>
                    </div>
                    <div class="input-group">
                        <label for="note_max">Note Maximale</label>
                        <input type="number" id="note_max" name="note_max" step="0.01" value="20" required>
                    </div>
                    <div class="input-group">
                        <label for="coefficient">Coefficient</label>
                        <input type="number" id="coefficient" name="coefficient" step="0.1" value="1" required>
                    </div>
                </div>
                <button type="submit" class="btn">Ajouter la Colonne</button>
            </form>
        </div>

        <!-- Liste des colonnes configurées -->
        <div class="list-container">
            <h3>Colonnes configurées</h3>
            <table>
                <thead>
                    <tr>
                        <th>Nom Colonne</th>
                        <th>Code Colonne</th>
                        <th>Type</th>
                        <th>Note Max</th>
                        <th>Coefficient</th>
                        <th>Obligatoire</th>
                        <th>Ordre</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($colonnes)): ?>
                        <tr><td colspan="8">Aucune colonne configurée pour cette matière et cette période.</td></tr>
                    <?php else: ?>
                        <?php foreach ($colonnes as $col): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($col['nom_colonne']); ?></td>
                                <td><?php echo htmlspecialchars($col['code_colonne']); ?></td>
                                <td><?php echo htmlspecialchars($col['type']); ?></td>
                                <td><?php echo htmlspecialchars($col['note_max']); ?></td>
                                <td><?php echo htmlspecialchars($col['coefficient']); ?></td>
                                <td><?php echo $col['obligatoire'] ? 'Oui' : 'Non'; ?></td>
                                <td><?php echo htmlspecialchars($col['ordre']); ?></td>
                                <td>
                                    <a href="<?php echo APP_URL; ?>/index.php?page=edit_column&id=<?php echo $col['id']; ?>" class="action-btn edit">Modifier</a>
                                    <form action="<?php echo APP_URL; ?>/index.php?action=delete_column" method="POST" style="display: inline;" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cette colonne ? Cette action est irréversible si aucune note n\'a été saisie.');">
                                        <input type="hidden" name="column_id" value="<?php echo $col['id']; ?>">
                                        <input type="hidden" name="matiere_id" value="<?php echo $matiere_id; ?>">
                                        <input type="hidden" name="periode_id" value="<?php echo $periode_id; ?>">
                                        <button type="submit" class="action-btn delete">Supprimer</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php
}
?>