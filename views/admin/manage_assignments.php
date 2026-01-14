<?php
$pdo = getDBConnection();

// --- Récupérer les listes pour les formulaires ---
$professeurs = $pdo->query("SELECT id, nom, prenom FROM utilisateurs WHERE role = 'professeur' ORDER BY nom, prenom")->fetchAll();
$matieres = $pdo->query("
    SELECT m.id, m.nom, f.nom as filiere_nom
    FROM matieres m
    JOIN filieres f ON m.filiere_id = f.id
    ORDER BY m.nom, f.nom
")->fetchAll();
$periodes = $pdo->query("SELECT id, nom FROM periodes ORDER BY date_debut_saisie DESC")->fetchAll();

// --- Récupérer les affectations existantes ---
$assignments = $pdo->query("
    SELECT 
        a.id,
        CONCAT(u.prenom, ' ', u.nom) as prof_nom,
        m.nom as matiere_nom,
        f.nom as filiere_nom,
        p.nom as periode_nom,
        a.groupe
    FROM affectations_profs a
    JOIN utilisateurs u ON a.professeur_id = u.id
    JOIN matieres m ON a.matiere_id = m.id
    JOIN filieres f ON m.filiere_id = f.id
    JOIN periodes p ON a.periode_id = p.id
    ORDER BY p.date_debut_saisie DESC, prof_nom, matiere_nom
")->fetchAll();

?>

<div class="page-container">
    <h2>Gestion des Affectations (Professeurs -> Matières)</h2>

    <!-- Messages de session -->
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
        <h3>Affecter un professeur à une matière</h3>
        <form action="<?php echo APP_URL; ?>/index.php?action=add_assignment" method="POST">

            <div class="form-grid">
                <div class="input-group">
                    <label for="professeur_id">Professeur</label>
                    <select name="professeur_id" required>
                        <option value="">-- Choisir --</option>
                        <?php foreach ($professeurs as $prof): ?>
                            <option value="<?php echo $prof['id']; ?>"><?php echo htmlspecialchars($prof['prenom'] . ' ' . $prof['nom']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="input-group">
                    <label for="matiere_id">Matière</label>
                    <select name="matiere_id" required>
                        <option value="">-- Choisir --</option>
                        <?php foreach ($matieres as $matiere): ?>
                            <option value="<?php echo $matiere['id']; ?>"><?php echo htmlspecialchars($matiere['nom'] . ' (' . $matiere['filiere_nom'] . ')'); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="input-group">
                    <label for="periode_id">Période</label>
                    <select name="periode_id" required>
                        <option value="">-- Choisir --</option>
                        <?php foreach ($periodes as $periode): ?>
                            <option value="<?php echo $periode['id']; ?>"><?php echo htmlspecialchars($periode['nom']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="input-group">
                    <label for="groupe">Groupe (Optionnel)</label>
                    <input type="text" name="groupe" placeholder="Ex: Groupe A, TP1">
                </div>
            </div>
            <button type="submit" class="btn">Créer l'affectation</button>
        </form>
    </div>

    <!-- Liste des affectations -->
    <div class="list-container">
        <h3>Affectations existantes</h3>
        <table>
            <thead>
                <tr>
                    <th>Professeur</th>
                    <th>Matière</th>
                    <th>Période</th>
                    <th>Groupe</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($assignments)): ?>
                    <tr><td colspan="5">Aucune affectation créée.</td></tr>
                <?php else: ?>
                    <?php foreach ($assignments as $assign): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($assign['prof_nom']); ?></td>
                            <td><?php echo htmlspecialchars($assign['matiere_nom'] . ' (' . $assign['filiere_nom'] . ')'); ?></td>
                            <td><?php echo htmlspecialchars($assign['periode_nom']); ?></td>
                            <td><?php echo htmlspecialchars($assign['groupe'] ?: 'Tous'); ?></td>
                            <td>
                                <form action="<?php echo APP_URL; ?>/index.php?action=delete_assignment" method="POST" style="display: inline;" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cette affectation ?');">
                                    <input type="hidden" name="assignment_id" value="<?php echo $assign['id']; ?>">
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

