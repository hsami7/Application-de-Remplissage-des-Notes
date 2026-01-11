<?php
// views/admin/manage_periods.php
authorize_user(['admin']);

$pdo = getDBConnection();
$stmt = $pdo->query("SELECT id, nom, code, annee_universitaire, type, date_debut_saisie, date_fin_saisie, statut FROM periodes ORDER BY annee_universitaire DESC, nom ASC");
$periodes = $stmt->fetchAll();

?>

<div class="page-container">
    <h2>Gestion des Périodes de Notation</h2>

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
        <h3>Ajouter une nouvelle Période</h3>
        <form action="<?php echo APP_URL; ?>/index.php?action=add_period" method="post">
            <div class="form-grid">
                <div class="input-group">
                    <label for="nom">Nom de la période</label>
                    <input type="text" id="nom" name="nom" required>
                </div>
                <div class="input-group">
                    <label for="code">Code de la période</label>
                    <input type="text" id="code" name="code" required>
                </div>
                <div class="input-group">
                    <label for="annee_universitaire">Année Universitaire (AAAA-AAAA)</label>
                    <input type="text" id="annee_universitaire" name="annee_universitaire" pattern="\d{4}-\d{4}" placeholder="ex: 2024-2025" required>
                </div>
                <div class="input-group">
                    <label for="type">Type</label>
                    <select id="type" name="type" required>
                        <option value="semestre">Semestre</option>
                        <option value="trimestre">Trimestre</option>
                        <option value="session">Session</option>
                        <option value="rattrapage">Rattrapage</option>
                    </select>
                </div>
                <div class="input-group">
                    <label for="date_debut_saisie">Date Début Saisie</label>
                    <input type="datetime-local" id="date_debut_saisie" name="date_debut_saisie" required>
                </div>
                <div class="input-group">
                    <label for="date_fin_saisie">Date Fin Saisie</label>
                    <input type="datetime-local" id="date_fin_saisie" name="date_fin_saisie" required>
                </div>
            </div>
            <button type="submit" class="btn">Ajouter la Période</button>
        </form>
    </div>

    <!-- Liste des périodes -->
    <div class="list-container">
        <h3>Périodes existantes</h3>
        <table>
            <thead>
                <tr>
                    <th>Nom</th>
                    <th>Code</th>
                    <th>Année Universitaire</th>
                    <th>Type</th>
                    <th>Début Saisie</th>
                    <th>Fin Saisie</th>
                    <th>Statut</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($periodes)): ?>
                    <tr><td colspan="8">Aucune période créée.</td></tr>
                <?php else: ?>
                    <?php foreach ($periodes as $periode): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($periode['nom']); ?></td>
                            <td><?php echo htmlspecialchars($periode['code']); ?></td>
                            <td><?php echo htmlspecialchars($periode['annee_universitaire']); ?></td>
                            <td><?php echo htmlspecialchars($periode['type']); ?></td>
                            <td><?php echo htmlspecialchars($periode['date_debut_saisie']); ?></td>
                            <td><?php echo htmlspecialchars($periode['date_fin_saisie']); ?></td>
                            <td><?php echo htmlspecialchars($periode['statut']); ?></td>
                            <td>
                                <a href="<?php echo APP_URL; ?>/index.php?page=edit_period&id=<?php echo $periode['id']; ?>" class="action-btn edit">Modifier</a>
                                <form action="<?php echo APP_URL; ?>/index.php?action=delete_period" method="POST" style="display: inline;" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cette période ?');">
                                    <input type="hidden" name="periode_id" value="<?php echo $periode['id']; ?>">
                                    <button type="submit" class="action-btn delete">Supprimer</button>
                                </form>
                                <form action="<?php echo APP_URL; ?>/index.php?action=change_period_status" method="POST" style="display: inline;">
                                    <input type="hidden" name="periode_id" value="<?php echo $periode['id']; ?>">
                                    <select name="statut" onchange="this.form.submit()" class="action-btn">
                                        <option value="a_venir" <?php echo ($periode['statut'] == 'a_venir') ? 'selected' : ''; ?>>À venir</option>
                                        <option value="ouverte" <?php echo ($periode['statut'] == 'ouverte') ? 'selected' : ''; ?>>Ouverte</option>
                                        <option value="fermee" <?php echo ($periode['statut'] == 'fermee') ? 'selected' : ''; ?>>Fermée</option>
                                        <option value="publiee" <?php echo ($periode['statut'] == 'publiee') ? 'selected' : ''; ?>>Publiée</option>
                                    </select>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>