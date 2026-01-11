<?php
// =============================================
// FONCTION DE CONNEXION À LA BASE DE DONNÉES
// =============================================

/**
 * Établit une connexion à la base de données en utilisant PDO.
 *
 * @return PDO|null Retourne une instance de PDO en cas de succès, ou null en cas d'échec.
 */
function getDBConnection() {
    // Options de connexion PDO
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Lève des exceptions en cas d'erreur
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // Récupère les résultats sous forme de tableau associatif
        PDO::ATTR_EMULATE_PREPARES   => false,                  // Utilise les vraies requêtes préparées
    ];

    // DSN (Data Source Name)
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;

    try {
        // Création de l'instance PDO
        $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        return $pdo;
    } catch (PDOException $e) {
        // En mode débogage, afficher l'erreur détaillée
        if (DEBUG_MODE) {
            // Afficher un message d'erreur plus convivial et informatif
            echo "<div style='border: 2px solid red; padding: 15px; margin: 20px; background-color: #ffebee; font-family: sans-serif;'>";
            echo "<h2>Erreur de Connexion à la Base de Données</h2>";
            echo "<p><strong>Message :</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
            echo "<p><strong>Fichier :</strong> " . htmlspecialchars($e->getFile()) . " (Ligne " . htmlspecialchars($e->getLine()) . ")</p>";
            echo "<hr>";
            echo "<h3>Vérifications suggérées :</h3>";
            echo "<ul>";
            echo "<li>Le serveur de base de données (<strong>" . DB_HOST . "</strong>) est-il démarré ?</li>";
            echo "<li>Le nom de la base de données (<strong>" . DB_NAME . "</strong>) est-il correct ?</li>";
            echo "<li>L'utilisateur (<strong>" . DB_USER . "</strong>) et le mot de passe sont-ils corrects ?</li>";
            echo "<li>Avez-vous importé le fichier <code>database/database.sql</code> dans votre base de données ?</li>";
            echo "</ul>";
            echo "</div>";
        } else {
            // En production, afficher un message générique et logger l'erreur
            error_log("Erreur de connexion à la BDD : " . $e->getMessage());
            // Rediriger vers une page d'erreur générique si nécessaire
            // header('Location: /error.php');
            echo "Une erreur de connexion est survenue. Veuillez réessayer plus tard.";
        }
        // Arrêter l'exécution du script en cas d'échec de la connexion
        exit;
    }
}

// Optionnel: instancier la connexion pour qu'elle soit disponible globalement
// $pdo = getDBConnection();

