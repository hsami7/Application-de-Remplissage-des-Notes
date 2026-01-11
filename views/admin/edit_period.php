<?php
// views/admin/edit_period.php
authorize_user(['admin']);

$pdo = getDBConnection();
$periode_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$periode_id) {
    $_SESSION['error_message'] = "ID de période non valide.";
    header('Location: ' . APP_URL . '/index.php?page=manage_periods');
    exit;
}

$stmt = $pdo->prepare("SELECT id, nom, annee_universitaire, type, date_debut_saisie, date_fin_saisie, statut FROM periodes WHERE id = ?");
$stmt->execute([$periode_id]);
$periode = $stmt->fetch();

if (!$periode) {
    $_SESSION['error_message'] = "Période non trouvée.";
    header('Location: ' . APP_URL . '/index.php?page=manage_periods');
    exit;
}

?>

<div class="page-container">
    <h2>Modifier la période: <?php echo htmlspecialchars($periode['nom']); ?></h2>

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
        <form action="<?php echo APP_URL; ?>/index.php?action=update_period" method="post">
            <input type="hidden" name="periode_id" value="<?php echo htmlspecialchars($periode['id']); ?>">
            
            <div class="form-grid">
                <div class="input-group">
                    <label for="nom">Nom de la période</label>
                    <input type="text" id="nom" name="nom" value="<?php echo htmlspecialchars($periode['nom']); ?>" required>
                </div>

                <div class="input-group">
                    <label for="annee_universitaire">Année Universitaire (AAAA-AAAA)</label>
                    <input type="text" id="annee_universitaire" name="annee_universitaire" pattern="\d{4}-\d{4}" value="<?php echo htmlspecialchars($periode['annee_universitaire']); ?>" required>
                </div>
                <div class="input-group">
                    <label for="type">Type</label>
                    <select id="type" name="type" required>
                        <option value="semestre" <?php echo ($periode['type'] == 'semestre') ? 'selected' : ''; ?>>Semestre</option>
                        <option value="trimestre" <?php echo ($periode['type'] == 'trimestre') ? 'selected' : ''; ?>>Trimestre</option>
                        <option value="session" <?php echo ($periode['type'] == 'session') ? 'selected' : ''; ?>>Session</option>
                        <option value="rattrapage" <?php echo ($periode['type'] == 'rattrapage') ? 'selected' : ''; ?>>Rattrapage</option>
                    </select>
                </div>
                <div class="input-group">
                    <label for="date_debut_saisie">Date Début Saisie</label>
                    <input type="datetime-local" id="date_debut_saisie" name="date_debut_saisie" value="<?php echo date('Y-m-d\TH:i', strtotime($periode['date_debut_saisie'])); ?>" required>
                </div>
                <div class="input-group">
                    <label for="date_fin_saisie">Date Fin Saisie</label>
                    <input type="datetime-local" id="date_fin_saisie" name="date_fin_saisie" value="<?php echo date('Y-m-d\TH:i', strtotime($periode['date_fin_saisie'])); ?>" required>
                </div>
            </div>
            <button type="submit" class="btn">Mettre à jour la période</button>
            <a href="<?php echo APP_URL; ?>/index.php?page=manage_periods" class="btn btn-secondary">Annuler</a>
        </form>
    </div>
</div>