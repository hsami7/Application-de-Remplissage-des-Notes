<?php
// Script de migration pour ajouter la colonne 'statut_validation' à la table 'moyennes'

// Inclure la configuration de la base de données et la fonction de connexion
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../core/functions/db_connect.php';

echo "Début de la migration: Ajout de la colonne 'statut_validation' à la table 'moyennes'.<br>";

try {
    $pdo = getDBConnection();
    
    // Vérifier si la colonne existe déjà pour éviter les erreurs
    $stmt = $pdo->query("SHOW COLUMNS FROM moyennes LIKE 'statut_validation'");
    $column_exists = $stmt->fetch();

    if ($column_exists) {
        echo "La colonne 'statut_validation' existe déjà dans la table 'moyennes'. Aucune action requise.<br>";
    } else {
        $sql = "ALTER TABLE moyennes ADD COLUMN statut_validation VARCHAR(50) DEFAULT 'non_validée' NOT NULL";
        $pdo->exec($sql);
        echo "La colonne 'statut_validation' a été ajoutée avec succès à la table 'moyennes'.<br>";
    }
    
    echo "Migration terminée avec succès.<br>";

} catch (PDOException $e) {
    echo "Erreur lors de la migration: " . $e->getMessage() . "<br>";
    if (DEBUG_MODE) {
        echo "<pre>" . $e->getTraceAsString() . "</pre>";
    }
} catch (Exception $e) {
    echo "Une erreur inattendue est survenue: " . $e->getMessage() . "<br>";
}
?>