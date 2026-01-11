<div class="login-container">
    <div class="login-box">
        <h2>Connexion</h2>
        <p>Connectez-vous pour accéder à votre espace</p>

        <?php
        // Afficher les messages d'erreur éventuels
        if (isset($_SESSION['error_message'])) {
            echo '<div class="error-message">' . htmlspecialchars($_SESSION['error_message']) . '</div>';
            unset($_SESSION['error_message']); // Nettoyer le message après affichage
        }
        ?>

        <form action="<?php echo APP_URL; ?>/index.php?action=login" method="POST">

            <div class="input-group">
                <label for="email">Adresse E-mail</label>
                <input type="email" id="email" name="email" placeholder="exemple@uemf.ma" required>
            </div>
            <div class="input-group">
                <label for="password">Mot de passe</label>
                <input type="password" id="password" name="password" required>
            </div>
            <div class="input-group">
                <button type="submit" class="btn-login">Se connecter</button>
            </div>
            <div class="login-footer">
                <a href="#">Mot de passe oublié ?</a>
            </div>
        </form>
    </div>
</div>
