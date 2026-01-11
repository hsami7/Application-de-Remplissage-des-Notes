<?php

require_once 'functions/security_functions.php';


// 1. Vider toutes les variables de session
$_SESSION = [];

// 2. Détruire la session
session_destroy();

// 3. Rediriger vers la page de connexion avec un message (optionnel)
// Vous pourriez ajouter un paramètre GET pour afficher un message de déconnexion réussie
header('Location: ' . APP_URL . '/index.php?page=login&status=logged_out');
exit;
