<?php
// views/admin/edit_column.php
authorize_user(['admin']);

$pdo = getDBConnection();

$column_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$column_id) {
    $_SESSION['error_message'] = "ID de colonne non valide.";
    header('Location: ' . APP_URL . '/index.php?page=configure_notes');
    exit;
}

// Fetch column data
$stmt = $pdo->prepare("SELECT * FROM configuration_colonnes WHERE id = ?");
$stmt->execute([$column_id]);
$column = $stmt->fetch();

if (!$column) {
    $_SESSION['error_message'] = "Colonne non trouvée.";
    header('Location: ' . APP_URL . '/index.php?page=configure_notes');
    exit;
}

// Fetch subject and period info for context
$stmt_context = $pdo->prepare("SELECT m.nom as matiere_nom, p.nom as periode_nom FROM matieres m, periodes p WHERE m.id = ? AND p.id = ?");
$stmt_context->execute([$column['matiere_id'], $column['periode_id']]);
$context = $stmt_context->fetch();

?>

<div class="page-container">
    <h2>Modifier la Colonne</h2>
    <p>Matière: <strong><?php echo htmlspecialchars($context['matiere_nom']); ?></strong> | Période: <strong><?php echo htmlspecialchars($context['periode_nom']); ?></strong></p>

    <div class="form-container">
        <form action="<?php echo APP_URL; ?>/index.php?action=update_column" method="post">
            <input type="hidden" name="column_id" value="<?php echo $column['id']; ?>">
            <input type="hidden" name="matiere_id" value="<?php echo $column['matiere_id']; ?>">
            <input type="hidden" name="periode_id" value="<?php echo $column['periode_id']; ?>">

            <div class="form-grid">
                <div class="input-group">
                    <label for="nom_colonne">Nom de la Colonne</label>
                    <input type="text" id="nom_colonne" name="nom_colonne" value="<?php echo htmlspecialchars($column['nom_colonne']); ?>" required>
                </div>
                <div class="input-group">
                    <label for="code_colonne">Code pour formule</label>
                    <input type="text" id="code_colonne" name="code_colonne" value="<?php echo htmlspecialchars($column['code_colonne']); ?>" required>
                </div>
                <div class="input-group">
                    <label for="type">Type</label>
                    <select id="type" name="type" required>
                        <option value="note" <?php echo ($column['type'] == 'note') ? 'selected' : ''; ?>>Note</option>
                        <option value="bonus" <?php echo ($column['type'] == 'bonus') ? 'selected' : ''; ?>>Bonus</option>
                        <option value="malus" <?php echo ($column['type'] == 'malus') ? 'selected' : ''; ?>>Malus</option>
                        <option value="info" <?php echo ($column['type'] == 'info') ? 'selected' : ''; ?>>Info (non calculable)</option>
                    </select>
                </div>
                <div class="input-group">
                    <label for="note_max">Note Maximale</label>
                    <input type="number" id="note_max" name="note_max" step="0.01" value="<?php echo htmlspecialchars($column['note_max']); ?>" required>
                </div>
                <div class="input-group">
                    <label for="coefficient">Coefficient</label>
                    <input type="number" id="coefficient" name="coefficient" step="0.1" value="<?php echo htmlspecialchars($column['coefficient']); ?>" required>
                </div>
                <div class="input-group">
                    <label for="obligatoire">Obligatoire</label>
                    <select id="obligatoire" name="obligatoire" required>
                        <option value="1" <?php echo ($column['obligatoire'] == 1) ? 'selected' : ''; ?>>Oui</option>
                        <option value="0" <?php echo ($column['obligatoire'] == 0) ? 'selected' : ''; ?>>Non</option>
                    </select>
                </div>
            </div>
            <div class="form-actions">
                <button type="submit" class="btn">Mettre à jour</button>
                <a href="<?php echo APP_URL; ?>/index.php?page=configure_notes&matiere_id=<?php echo $column['matiere_id']; ?>&periode_id=<?php echo $column['periode_id']; ?>" class="btn btn-back">Annuler</a>
            </div>
        </form>
    </div>
</div>
