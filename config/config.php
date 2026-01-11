<?php

// =============================================
// CONFIGURATION DE LA BASE DE DONNÉES
// =============================================
// Remplacez ces valeurs par vos propres informations de connexion à la base de données.

/**
 * Hôte de la base de données (ex: "localhost" ou "127.0.0.1")
 */
define('DB_HOST', 'localhost');

/**
 * Nom de la base de données
 */
define('DB_NAME', 'notes_db');

/**
 * Utilisateur de la base de données
 */
define('DB_USER', 'root');

/**
 * Mot de passe de la base de données
 */
define('DB_PASS', '');

/**
 * Jeu de caractères à utiliser pour la connexion à la base de données
 */
define('DB_CHARSET', 'utf8mb4');


// =============================================
// CONFIGURATION DE L'APPLICATION
// =============================================

/**
 * URL racine de l'application (à adapter à votre environnement)
 * Ex: http://localhost/mon-projet/
 */
define('APP_URL', 'http://localhost/Application-de-Remplissage-des-Notes/public');

/**
 * Nom de l'application
 */
define('APP_NAME', 'Gestion des Notes');

/**
 * Mode de débogage. Mettre à `false` en production.
 * Si `true`, les erreurs PHP seront affichées.
 */
define('DEBUG_MODE', true);

// Configuration de l'affichage des erreurs en fonction du mode de débogage
if (DEBUG_MODE) {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', 0);
    ini_set('display_startup_errors', 0);
    error_reporting(0);
}

