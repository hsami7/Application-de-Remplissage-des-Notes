<?php
// views/admin/manage_formulas.php
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
        <h2>Gestion des Formules</h2>
        <h3>Sélectionnez une matière et une période</h3>

        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="message error">
                <?php echo htmlspecialchars($_SESSION['error_message']); unset($_SESSION['error_message']); ?>
            </div>
        <?php endif; ?>

        <div class="form-container">
            <form action="<?php echo APP_URL; ?>/index.php" method="GET">
                <input type="hidden" name="page" value="manage_formulas">
                <div class="form-grid">
                    <div class="input-group">
                        <label for="matiere_id_select">Matière</label>
                        <select id="matiere_id_select" name="matiere_id" required>
                            <option value="">-- Sélectionner une matière --</option>
                            <?php foreach ($all_matieres as $matiere): ?>
                                <option value="<?php echo $matiere['id']; ?>"><?php echo htmlspecialchars($matiere['nom']) . ' (' . htmlspecialchars($matiere['filiere_nom']) . ')'; ?></option>
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
                <button type="submit" class="btn">Afficher la Formule</button>
            </form>
        </div>
    </div>
    <?php
} else {
    // Proceed to display formula configuration if IDs are present

    // Fetch subject and period info
    $stmt_context = $pdo->prepare("SELECT m.nom as matiere_nom, p.nom as periode_nom FROM matieres m JOIN periodes p ON p.id = ? WHERE m.id = ?");
    $stmt_context->execute([$periode_id, $matiere_id]);
    $context = $stmt_context->fetch();

    if (!$context) {
        $_SESSION['error_message'] = "Matière ou période introuvable.";
        header('Location: ' . APP_URL . '/index.php?page=dashboard_admin');
        exit;
    }

    // Fetch existing formula
    $stmt_formula = $pdo->prepare("SELECT formule FROM formules WHERE matiere_id = ? AND periode_id = ?");
    $stmt_formula->execute([$matiere_id, $periode_id]);
    $current_formula = $stmt_formula->fetchColumn();

    // Fetch available column codes for formula building
    $stmt_cols = $pdo->prepare("SELECT code_colonne FROM configuration_colonnes WHERE matiere_id = ? AND periode_id = ? ORDER BY ordre ASC");
    $stmt_cols->execute([$matiere_id, $periode_id]);
    $available_columns = $stmt_cols->fetchAll(PDO::FETCH_COLUMN);

    ?>

    <div class="page-container">
        <h2>Gestion de la Formule de Calcul</h2>
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

        <div class="form-container">
            <h3>Définir/Modifier la formule</h3>
            <form action="<?php echo APP_URL; ?>/index.php?action=save_formula" method="post">
                <input type="hidden" name="matiere_id" value="<?php echo htmlspecialchars($matiere_id); ?>">
                <input type="hidden" name="periode_id" value="<?php echo htmlspecialchars($periode_id); ?>">

                <div class="input-group">
                    <label for="formule">Formule de Calcul</label>
                    <textarea id="formule" name="formule" rows="5" required><?php echo htmlspecialchars($current_formula ?? ''); ?></textarea>
                    <small>Utilisez les codes de colonne disponibles ci-dessous et les opérateurs (+ - * / ()), fonctions (MAX, MIN, MOYENNE, SI).</small>
                </div>

                <div class="input-group">
                    <label>Variables disponibles:</label>
                    <div class="available-vars">
                        <?php if (empty($available_columns)): ?>
                            <p>Aucune colonne configurée pour cette matière. Configurez les colonnes d'abord.</p>
                        <?php else: ?>
                            <?php foreach ($available_columns as $col_code): ?>
                                <span class="var-tag"><?php echo htmlspecialchars($col_code); ?></span>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <button type="submit" class="btn">Enregistrer la Formule</button>
                <a href="<?php echo APP_URL; ?>/index.php?page=configure_notes&matiere_id=<?php echo $matiere_id; ?>&periode_id=<?php echo $periode_id; ?>" class="btn btn-secondary">Retour à la configuration des notes</a>
            </form>
        </div>

        <div class="form-container" style="margin-top: 2rem;">
            <h3>Calculer les moyennes (pour test)</h3>
            <form action="<?php echo APP_URL; ?>/index.php?action=calculate_averages" method="post">
                <input type="hidden" name="matiere_id" value="<?php echo htmlspecialchars($matiere_id); ?>">
                <input type="hidden" name="periode_id" value="<?php echo htmlspecialchars($periode_id); ?>">
                <button type="submit" class="btn btn-info" onclick="return confirm('Ceci va recalculer et enregistrer les moyennes pour cette matière et période. Continuer?');">Calculer et Tester les Moyennes</button>
            </form>
        </div>
    </div>
    <?php
}
?>