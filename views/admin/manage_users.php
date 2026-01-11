<?php
$pdo = getDBConnection();

// --- Récupérer les utilisateurs existants ---
$users = $pdo->query("SELECT id, nom, prenom, email, role FROM utilisateurs ORDER BY nom, prenom")->fetchAll();

?>

<div class="page-container">
    <h2>Gestion des Utilisateurs</h2>

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
        <h3>Ajouter un utilisateur</h3>
        <form action="<?php echo APP_URL; ?>/index.php?action=add_user" method="POST">

            <div class="form-grid">
                <div class="input-group">
                    <label for="prenom">Prénom</label>
                    <input type="text" name="prenom" required>
                </div>
                <div class="input-group">
                    <label for="nom">Nom</label>
                    <input type="text" name="nom" required>
                </div>
                <div class="input-group">
                    <label for="email">Email</label>
                    <input type="email" name="email" required>
                </div>
                <div class="input-group">
                    <label for="mot_de_passe">Mot de passe</label>
                    <input type="password" name="mot_de_passe" required>
                </div>
                <div class="input-group">
                    <label for="role">Rôle</label>
                    <select name="role" required>
                        <option value="etudiant">Étudiant</option>
                        <option value="professeur">Professeur</option>
                        <option value="admin">Administrateur</option>
                    </select>
                </div>
            </div>
            <button type="submit" class="btn">Ajouter l'utilisateur</button>
        </form>
    </div>

    <!-- Liste des utilisateurs -->
    <div class="list-container">
        <h3>Utilisateurs existants</h3>
        <table>
            <thead>
                <tr>
                    <th>Prénom</th>
                    <th>Nom</th>
                    <th>Email</th>
                    <th>Rôle</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($users)): ?>
                    <tr><td colspan="5">Aucun utilisateur créé.</td></tr>
                <?php else: ?>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($user['prenom']); ?></td>
                            <td><?php echo htmlspecialchars($user['nom']); ?></td>
                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                            <td><?php echo htmlspecialchars(ucfirst($user['role'])); ?></td>
                            <td>
                                <a href="<?php echo APP_URL; ?>/index.php?page=edit_user&id=<?php echo $user['id']; ?>" class="action-btn edit">Modifier</a>
                                <form action="<?php echo APP_URL; ?>/index.php?action=delete_user" method="POST" style="display: inline;" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cet utilisateur ?');">
                                    <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
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
