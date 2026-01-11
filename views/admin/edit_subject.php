<?php
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['error_message'] = "ID de matière manquant ou invalide.";
    header('Location: ' . APP_URL . '/index.php?page=manage_subjects');
    exit;
}

$matiere_id = $_GET['id'];
$pdo = getDBConnection();

// Récupérer les détails de la matière
$stmt = $pdo->prepare("SELECT id, nom, filiere_id, coefficient, seuil_validation FROM matieres WHERE id = ?");
$stmt->execute([$matiere_id]);
$matiere = $stmt->fetch();

if (!$matiere) {
    $_SESSION['error_message'] = "Matière non trouvée.";
    header('Location: ' . APP_URL . '/index.php?page=manage_subjects');
    exit;
}

// Récupérer la liste des filières pour le sélecteur
$filieres_stmt = $pdo->query("SELECT id, nom FROM filieres ORDER BY nom");
$filieres = $filieres_stmt->fetchAll();
?>

<div class="page-container">
    <h2>Modifier la matière: <?php echo htmlspecialchars($matiere['nom']); ?></h2>

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
        <form action="<?php echo APP_URL; ?>/index.php?action=update_subject" method="POST">

            <input type="hidden" name="id" value="<?php echo htmlspecialchars($matiere['id']); ?>">
            <div class="form-grid">
                <div class="input-group">
                    <label for="nom">Nom de la matière</label>
                    <input type="text" id="nom" name="nom" value="<?php echo htmlspecialchars($matiere['nom']); ?>" required>
                </div>
                <div class="input-group">
                    <label for="filiere_id">Filière</label>
                    <select id="filiere_id" name="filiere_id" required>
                        <option value="">-- Sélectionner une filière --</option>
                        <?php foreach ($filieres as $filiere): ?>
                            <option value="<?php echo $filiere['id']; ?>" <?php echo ($filiere['id'] == $matiere['filiere_id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($filiere['nom']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="input-group">
                    <label for="coefficient">Coefficient</label>
                    <input type="number" id="coefficient" name="coefficient" step="0.1" value="<?php echo htmlspecialchars($matiere['coefficient']); ?>" required>
                </div>
                 <div class="input-group">
                    <label for="seuil_validation">Seuil de validation</label>
                    <input type="number" id="seuil_validation" name="seuil_validation" step="0.01" value="<?php echo htmlspecialchars($matiere['seuil_validation']); ?>" required>
                </div>
            </div>
            <button type="submit" class="btn">Mettre à jour la matière</button>
            <a href="<?php echo APP_URL; ?>/index.php?page=manage_subjects" class="btn btn-secondary">Annuler</a>
        </form>
    </div>
</div>