<?php
// views/admin/manage_filieres.php
authorize_user(['admin']);

$pdo = getDBConnection();

// Fetch filieres and their responsables in one query
$stmt = $pdo->query("
    SELECT f.id, f.nom, f.code, f.niveau, u.prenom AS resp_prenom, u.nom AS resp_nom
    FROM filieres f
    LEFT JOIN utilisateurs u ON f.responsable_id = u.id
    ORDER BY f.nom
");
$filieres = $stmt->fetchAll();

// Fetch users with 'professeur' role for the dropdown
$users_stmt = $pdo->query("SELECT id, nom, prenom FROM utilisateurs WHERE role = 'professeur' ORDER BY nom, prenom");
$responsables = $users_stmt->fetchAll();

?>

<div class="page-container">
    <h2>Gestion des Filières</h2>

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
        <h3>Ajouter une nouvelle Filière</h3>
        <form action="<?php echo APP_URL; ?>/index.php?action=add_filiere" method="post">
            <div class="form-grid">
                <div class="input-group">
                    <label for="nom">Nom de la filière</label>
                    <input type="text" id="nom" name="nom" required>
                </div>
                <div class="input-group">
                    <label for="code">Code de la filière</label>
                    <input type="text" id="code" name="code" required>
                </div>
                <div class="input-group">
                    <label for="niveau">Niveau</label>
                    <input type="text" id="niveau" name="niveau">
                </div>
                <div class="input-group">
                    <label for="responsable_id">Responsable</label>
                    <select id="responsable_id" name="responsable_id">
                        <option value="">-- Sélectionner un responsable --</option>
                        <?php foreach ($responsables as $resp): ?>
                            <option value="<?php echo $resp['id']; ?>"><?php echo htmlspecialchars($resp['prenom'] . ' ' . $resp['nom']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <button type="submit" class="btn">Ajouter la Filière</button>
        </form>
    </div>

    <!-- Liste des filières -->
    <div class="list-container">
        <h3>Filières existantes</h3>
        <table>
            <thead>
                <tr>
                    <th>Nom</th>
                    <th>Code</th>
                    <th>Niveau</th>
                    <th>Responsable</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($filieres)): ?>
                    <tr><td colspan="5">Aucune filière créée.</td></tr>
                <?php else: ?>
                    <?php foreach ($filieres as $filiere): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($filiere['nom']); ?></td>
                            <td><?php echo htmlspecialchars($filiere['code']); ?></td>
                            <td><?php echo htmlspecialchars($filiere['niveau']); ?></td>
                            <td><?php
                                if (!empty($filiere['resp_prenom'])) {
                                    echo htmlspecialchars($filiere['resp_prenom'] . ' ' . $filiere['resp_nom']);
                                } else {
                                    echo '<span style="color: #999;">Non assigné</span>';
                                }
                            ?></td>
                            <td>
                                <!-- Add edit/delete actions here -->
                                <a href="<?php echo APP_URL; ?>/index.php?page=edit_filiere&id=<?php echo $filiere['id']; ?>" class="action-btn edit">Modifier</a>
                                <form action="<?php echo APP_URL; ?>/index.php?action=delete_filiere" method="POST" style="display: inline;" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cette filière ?');">
                                    <input type="hidden" name="filiere_id" value="<?php echo $filiere['id']; ?>">
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