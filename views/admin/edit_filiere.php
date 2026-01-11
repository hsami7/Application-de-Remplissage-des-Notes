<?php
// views/admin/edit_filiere.php
authorize_user(['admin']);

$pdo = getDBConnection();

$filiere_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$filiere_id) {
    $_SESSION['error_message'] = "ID de filière manquant ou invalide.";
    header('Location: ' . APP_URL . '/index.php?page=manage_filieres');
    exit;
}

// Récupérer les détails de la filière
$stmt = $pdo->prepare("SELECT id, nom, niveau, responsable_id FROM filieres WHERE id = ?");
$stmt->execute([$filiere_id]);
$filiere = $stmt->fetch();

if (!$filiere) {
    $_SESSION['error_message'] = "Filière non trouvée.";
    header('Location: ' . APP_URL . '/index.php?page=manage_filieres');
    exit;
}

// Récupérer la liste des responsables (professeurs) pour le sélecteur
$users_stmt = $pdo->query("SELECT id, nom, prenom FROM utilisateurs WHERE role = 'professeur' ORDER BY nom, prenom");
$responsables = $users_stmt->fetchAll();

?>

<div class="page-container">
    <h2>Modifier la filière: <?php echo htmlspecialchars($filiere['nom']); ?></h2>

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
        <form action="<?php echo APP_URL; ?>/index.php?action=update_filiere" method="POST">

            <input type="hidden" name="filiere_id" value="<?php echo htmlspecialchars($filiere['id']); ?>">
            <div class="form-grid">
                <div class="input-group">
                    <label for="nom">Nom de la filière</label>
                    <input type="text" id="nom" name="nom" value="<?php echo htmlspecialchars($filiere['nom']); ?>" required>
                </div>

                <div class="input-group">
                    <label for="niveau">Niveau</label>
                    <input type="text" id="niveau" name="niveau" value="<?php echo htmlspecialchars($filiere['niveau']); ?>">
                </div>
                <div class="input-group">
                    <label for="responsable_id">Responsable</label>
                    <select id="responsable_id" name="responsable_id">
                        <option value="">-- Sélectionner un responsable --</option>
                        <?php foreach ($responsables as $resp): ?>
                            <option value="<?php echo $resp['id']; ?>" <?php echo ($resp['id'] == $filiere['responsable_id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($resp['prenom'] . ' ' . $resp['nom']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <button type="submit" class="btn">Mettre à jour la filière</button>
            <a href="<?php echo APP_URL; ?>/index.php?page=manage_filieres" class="btn btn-secondary">Annuler</a>
        </form>
    </div>
</div>
