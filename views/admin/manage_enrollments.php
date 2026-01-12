<?php
$pdo = getDBConnection();

// --- Récupérer les listes pour les formulaires ---
$etudiants = $pdo->query("SELECT id, nom, prenom FROM utilisateurs WHERE role = 'etudiant' ORDER BY nom, prenom")->fetchAll();
$matieres = $pdo->query("
    SELECT m.id, m.nom, f.nom as filiere_nom
    FROM matieres m
    JOIN filieres f ON m.filiere_id = f.id
    ORDER BY m.nom, f.nom
")->fetchAll();
$periodes = $pdo->query("SELECT id, nom FROM periodes ORDER BY date_debut_saisie DESC")->fetchAll();

// --- Récupérer les inscriptions existantes ---
$enrollments = $pdo->query("
    SELECT 
        i.id,
        CONCAT(u.prenom, ' ', u.nom) as etudiant_nom,
        m.nom as matiere_nom,
        p.nom as periode_nom,
        i.groupe
    FROM inscriptions_matieres i
    JOIN utilisateurs u ON i.etudiant_id = u.id
    JOIN matieres m ON i.matiere_id = m.id
    JOIN periodes p ON i.periode_id = p.id
    ORDER BY p.date_debut_saisie DESC, etudiant_nom, matiere_nom
")->fetchAll();

?>

<div class="page-container">
    <h2>Gestion des Inscriptions (Étudiants -> Matières)</h2>

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
        <h3>Inscrire un étudiant à une matière</h3>
        <form action="<?php echo APP_URL; ?>/index.php?action=add_enrollment" method="POST">

            <div class="form-grid">
                <div class="input-group">
                    <label for="etudiant_id">Étudiant</label>
                    <select name="etudiant_id" required>
                        <option value="">-- Choisir --</option>
                        <?php foreach ($etudiants as $etudiant): ?>
                            <option value="<?php echo $etudiant['id']; ?>"><?php echo htmlspecialchars($etudiant['prenom'] . ' ' . $etudiant['nom']); ?></option>
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
            <button type="submit" class="btn">Créer l'inscription</button>
        </form>
    </div>

    <!-- Liste des inscriptions -->
    <div class="list-container">
        <h3>Inscriptions existantes</h3>
        <table>
            <thead>
                <tr>
                    <th>Étudiant</th>
                    <th>Matière</th>
                    <th>Période</th>
                    <th>Groupe</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($enrollments)): ?>
                    <tr><td colspan="5">Aucune inscription créée.</td></tr>
                <?php else: ?>
                    <?php foreach ($enrollments as $enroll): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($enroll['etudiant_nom']); ?></td>
                            <td><?php echo htmlspecialchars($enroll['matiere_nom']); ?></td>
                            <td><?php echo htmlspecialchars($enroll['periode_nom']); ?></td>
                            <td><?php echo htmlspecialchars($enroll['groupe'] ?: 'N/A'); ?></td>
                            <td>
                                <a href="#" class="action-btn delete">Supprimer</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

