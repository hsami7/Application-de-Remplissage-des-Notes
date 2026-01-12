<?php
// Script de migration pour ajouter la colonne 'code' à la table 'matieres'

// Inclure la configuration de la base de données et la fonction de connexion
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../core/functions/db_connect.php';

echo "Début de la migration: Ajout de la colonne 'code' à la table 'matieres'.<br>";

try {
    $pdo = getDBConnection();
    
    // Vérifier si la colonne existe déjà pour éviter les erreurs
    $stmt = $pdo->query("SHOW COLUMNS FROM matieres LIKE 'code'");
    $column_exists = $stmt->fetch();

    if ($column_exists) {
        echo "La colonne 'code' existe déjà dans la table 'matieres'. Aucune action requise.<br>";
    } else {
        // Ajouter la colonne 'code' de type VARCHAR(50) et permettre qu'elle soit NULL initialement
        // ou définir une valeur par défaut si toutes les matières doivent avoir un code.
        // Pour les données existantes, elles seront NULL ou vides.
        $sql = "ALTER TABLE matieres ADD COLUMN code VARCHAR(50) DEFAULT NULL AFTER nom";
        $pdo->exec($sql);
        echo "La colonne 'code' a été ajoutée avec succès à la table 'matieres'.<br>";
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