<?php
// Sécurité
if (!isset($_SESSION['authenticated']) || $_SESSION['user_role'] !== 'admin') {
    $_SESSION['error_message'] = "Accès non autorisé.";
    header('Location: ' . APP_URL . '/index.php?page=login');
    exit;
}

$pdo = getDBConnection();
$user_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($user_id === 0) {
    $_SESSION['error_message'] = "ID utilisateur non valide.";
    header('Location: ' . APP_URL . '/index.php?page=manage_users');
    exit;
}

$stmt = $pdo->prepare("SELECT id, nom, prenom, email, role FROM utilisateurs WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

if (!$user) {
    $_SESSION['error_message'] = "Utilisateur non trouvé.";
    header('Location: ' . APP_URL . '/index.php?page=manage_users');
    exit;
}
?>

<div class="page-container">
    <h2>Modifier l'utilisateur: <?php echo htmlspecialchars($user['prenom'] . ' ' . $user['nom']); ?></h2>

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
        <form action="<?php echo APP_URL; ?>/index.php?action=update_user" method="POST">
            <input type="hidden" name="id" value="<?php echo htmlspecialchars($user['id']); ?>">
            <div class="form-grid">
                <div class="input-group">
                    <label for="prenom">Prénom</label>
                    <input type="text" id="prenom" name="prenom" value="<?php echo htmlspecialchars($user['prenom']); ?>" required>
                </div>
                <div class="input-group">
                    <label for="nom">Nom</label>
                    <input type="text" id="nom" name="nom" value="<?php echo htmlspecialchars($user['nom']); ?>" required>
                </div>
                <div class="input-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                </div>
                <div class="input-group">
                    <label for="role">Rôle</label>
                    <select id="role" name="role" required>
                        <option value="etudiant" <?php if ($user['role'] == 'etudiant') echo 'selected'; ?>>Étudiant</option>
                        <option value="professeur" <?php if ($user['role'] == 'professeur') echo 'selected'; ?>>Professeur</option>
                        <option value="admin" <?php if ($user['role'] == 'admin') echo 'selected'; ?>>Administrateur</option>
                    </select>
                </div>
            </div>
            <button type="submit" class="btn">Mettre à jour l'utilisateur</button>
            <a href="<?php echo APP_URL; ?>/index.php?page=manage_users" class="btn btn-secondary">Annuler</a>
        </form>
    </div>
</div>
