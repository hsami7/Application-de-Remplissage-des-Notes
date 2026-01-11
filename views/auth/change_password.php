<?php
// This file is now protected by the router in public/index.php
?>

<div class="page-container">
    <h2>Changer le mot de passe</h2>

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

    <div class="form-container">
        <form action="<?php echo APP_URL; ?>/index.php?action=change_password" method="POST">

            <div class="input-group">
                <label for="current_password">Mot de passe actuel</label>
                <input type="password" name="current_password" required>
            </div>
            <div class="input-group">
                <label for="new_password">Nouveau mot de passe</label>
                <input type="password" name="new_password" required>
            </div>
            <div class="input-group">
                <label for="confirm_password">Confirmer le nouveau mot de passe</label>
                <input type="password" name="confirm_password" required>
            </div>
            <button type="submit" class="btn">Changer le mot de passe</button>
        </form>
    </div>
</div>
