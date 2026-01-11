<?php
// Sécurité
if (!isset($_SESSION['authenticated']) || $_SESSION['user_role'] !== 'admin') {
    $_SESSION['error_message'] = "Accès non autorisé.";
    header('Location: ' . APP_URL . '/index.php?page=login');
    exit;
}

$pdo = getDBConnection();

// --- Récupérer les listes pour les formulaires ---
// Liste des filières pour le champ "filiere_id"
$filieres_stmt = $pdo->query("SELECT id, nom FROM filieres ORDER BY nom");
$filieres = $filieres_stmt->fetchAll();

// Liste des matières existantes avec le nom de la filière
$subjects_stmt = $pdo->query("
    SELECT m.id, m.nom, m.filiere_id, m.coefficient, m.seuil_validation, f.nom as filiere_nom 
    FROM matieres m 
    JOIN filieres f ON m.filiere_id = f.id 
    ORDER BY f.nom, m.nom
");
$matieres = $subjects_stmt->fetchAll();

?>

<div class="page-container">
    <h2>Gestion des Matières</h2>

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
        <h3>Ajouter une nouvelle matière</h3>
        <form action="<?php echo APP_URL; ?>/index.php?action=add_subject" method="POST">

            <div class="form-grid">
                <div class="input-group">
                    <label for="nom">Nom de la matière</label>
                    <input type="text" id="nom" name="nom" required>
                </div>

                <div class="input-group">
                    <label for="filiere_id">Filière</label>
                    <select id="filiere_id" name="filiere_id" required>
                        <option value="">-- Sélectionner une filière --</option>
                        <?php foreach ($filieres as $filiere): ?>
                            <option value="<?php echo $filiere['id']; ?>"><?php echo htmlspecialchars($filiere['nom']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="input-group">
                    <label for="coefficient">Coefficient</label>
                    <input type="number" id="coefficient" name="coefficient" step="0.1" value="1.0" required>
                </div>
                 <div class="input-group">
                    <label for="seuil_validation">Seuil de validation</label>
                    <input type="number" id="seuil_validation" name="seuil_validation" step="0.01" value="10.00" required>
                </div>
            </div>
            <button type="submit" class="btn">Ajouter la matière</button>
        </form>
    </div>

    <!-- Liste des matières -->
    <div class="list-container">
        <h3>Matières existantes</h3>
        <table>
            <thead>
                <tr>
                    <th>Nom</th>

                    <th>Filière</th>
                    <th>Coeff.</th>
                    <th>Seuil</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($matieres)): ?>
                    <tr><td colspan="5">Aucune matière créée.</td></tr>
                <?php else: ?>
                    <?php foreach ($matieres as $matiere): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($matiere['nom']); ?></td>

                            <td><?php echo htmlspecialchars($matiere['filiere_nom']); ?></td>
                            <td><?php echo htmlspecialchars($matiere['coefficient']); ?></td>
                            <td><?php echo htmlspecialchars($matiere['seuil_validation']); ?></td>
                            <td>
                                <a href="<?php echo APP_URL; ?>/index.php?page=edit_subject&id=<?php echo $matiere['id']; ?>" class="action-btn edit">Modifier</a>
                                <form action="<?php echo APP_URL; ?>/index.php?action=delete_subject" method="POST" style="display: inline;" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cette matière ?');">
                                    <input type="hidden" name="matiere_id" value="<?php echo $matiere['id']; ?>">
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
