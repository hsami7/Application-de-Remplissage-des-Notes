<?php
// Ce fichier gérera différentes actions des professeurs

/**
 * Gère la sauvegarde des notes saisies dans la grille.
 */
function handle_save_grades() {
    // Validation des données de base
    $prof_id = $_SESSION['user_id'];
    $periode_id = filter_input(INPUT_POST, 'periode_id', FILTER_VALIDATE_INT);
    $matiere_id = filter_input(INPUT_POST, 'matiere_id', FILTER_VALIDATE_INT);
    $grades = $_POST['grades'] ?? [];

    $redirect_url = APP_URL . "/index.php?page=enter_grades&periode_id=$periode_id&matiere_id=$matiere_id";

    if (!$periode_id || !$matiere_id || empty($grades)) {
        $_SESSION['error_message'] = "Données manquantes pour l'enregistrement.";
        header('Location: ' . $redirect_url);
        exit;
    }

    try {
        $pdo = getDBConnection();

        // Vérifier que le professeur est bien affecté à cette matière pour cette période.
        $stmt_check_auth = $pdo->prepare("SELECT id FROM affectations_profs WHERE professeur_id = ? AND matiere_id = ? AND periode_id = ?");
        $stmt_check_auth->execute([$prof_id, $matiere_id, $periode_id]);
        if ($stmt_check_auth->rowCount() == 0) {
            $_SESSION['error_message'] = "Vous n'êtes pas autorisé à modifier les notes pour cette matière.";
            header('Location: ' . APP_URL . '/index.php?page=enter_grades');
            exit;
        }
        
        $pdo->beginTransaction();

        // Préparer les requêtes (UPSERT)
        $sql_check = "SELECT id FROM notes WHERE etudiant_id = ? AND colonne_id = ?";
        $sql_update = "UPDATE notes SET valeur = ?, statut = ?, saisi_par = ? WHERE id = ?";
        $sql_insert = "INSERT INTO notes (etudiant_id, colonne_id, valeur, statut, saisi_par) VALUES (?, ?, ?, ?, ?)";
        
        $stmt_check = $pdo->prepare($sql_check);
        $stmt_update = $pdo->prepare($sql_update);
        $stmt_insert = $pdo->prepare($sql_insert);

        // Récupérer les informations sur les colonnes pour la validation
        $stmt_cols = $pdo->prepare("SELECT id, note_max FROM configuration_colonnes WHERE matiere_id = ? AND periode_id = ?");
        $stmt_cols->execute([$matiere_id, $periode_id]);
        $colonnes_info = $stmt_cols->fetchAll(PDO::FETCH_KEY_PAIR);

        foreach ($grades as $etudiant_id => $colonnes) {
            foreach ($colonnes as $colonne_id => $valeur) {
                
                $valeur_numeric = null;
                $statut = 'saisie';

                // Ignorer les champs vides
                if ($valeur === '') continue;

                // Gérer les statuts spéciaux
                $valeur_upper = strtoupper(trim($valeur));
                if ($valeur_upper === 'ABS') {
                    $statut = 'absent';
                } elseif ($valeur_upper === 'DIS') {
                    $statut = 'dispense';
                } elseif ($valeur_upper === 'DEF') {
                    $statut = 'defaillant';
                } else {
                    $valeur_float = floatval(str_replace(',', '.', $valeur));
                    $note_max = $colonnes_info[$colonne_id] ?? 20;

                    if (!is_numeric(str_replace(',', '.', $valeur))) {
                        throw new Exception("La valeur '" . htmlspecialchars($valeur) . "' n'est pas une note valide.");
                    }
                    if ($valeur_float < 0 || $valeur_float > $note_max) {
                        throw new Exception("La note " . htmlspecialchars($valeur) . " doit être comprise entre 0 et " . $note_max . ".");
                    }
                    $valeur_numeric = $valeur_float;
                }

                // Vérifier si une note existe déjà
                $stmt_check->execute([$etudiant_id, $colonne_id]);
                $existing_note_id = $stmt_check->fetchColumn();

                if ($existing_note_id) {
                    // Update
                    $stmt_update->execute([$valeur_numeric, $statut, $prof_id, $existing_note_id]);
                } else {
                    // Insert
                    $stmt_insert->execute([$etudiant_id, $colonne_id, $valeur_numeric, $statut, $prof_id]);
                }
            }
        }

        $pdo->commit();
        $_SESSION['success_message'] = "Les notes ont été enregistrées avec succès.";

    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        $_SESSION['error_message'] = "Erreur : " . $e->getMessage();
    }

    // Redirection
    header('Location: ' . $redirect_url);
    exit;
}

/**
 * Gère la validation de la saisie des notes par le professeur.
 */
function handle_validate_grades() {
    // Validation des données
    $prof_id = $_SESSION['user_id'];
    $periode_id = filter_input(INPUT_POST, 'periode_id', FILTER_VALIDATE_INT);
    $matiere_id = filter_input(INPUT_POST, 'matiere_id', FILTER_VALIDATE_INT);

    $redirect_url = APP_URL . "/index.php?page=enter_grades&periode_id=$periode_id&matiere_id=$matiere_id";

    if (!$periode_id || !$matiere_id) {
        header('Location: ' . APP_URL . '/index.php?page=enter_grades');
        exit;
    }

    // Vérification de la complétude
    try {
        $pdo = getDBConnection();

        // Vérifier que le professeur est bien affecté à cette matière pour cette période.
        $stmt_check_auth = $pdo->prepare("SELECT id FROM affectations_profs WHERE professeur_id = ? AND matiere_id = ? AND periode_id = ?");
        $stmt_check_auth->execute([$prof_id, $matiere_id, $periode_id]);
        if ($stmt_check_auth->rowCount() == 0) {
            throw new Exception("Vous n'êtes pas autorisé à valider les notes pour cette matière.");
        }
        
        // Compter le nombre de notes attendues de manière fiable
        $stmt_etudiants = $pdo->prepare("SELECT COUNT(*) FROM inscriptions_matieres WHERE matiere_id = ? AND periode_id = ?");
        $stmt_etudiants->execute([$matiere_id, $periode_id]);
        $nombre_etudiants = $stmt_etudiants->fetchColumn();

        $stmt_colonnes = $pdo->prepare("SELECT COUNT(*) FROM configuration_colonnes WHERE matiere_id = ? AND periode_id = ?");
        $stmt_colonnes->execute([$matiere_id, $periode_id]);
        $nombre_colonnes = $stmt_colonnes->fetchColumn();

        $notes_attendues = $nombre_etudiants * $nombre_colonnes;

        // Compter le nombre de notes saisies
        $stmt_saisies = $pdo->prepare("
            SELECT COUNT(id)
            FROM notes
            WHERE colonne_id IN (SELECT id FROM configuration_colonnes WHERE matiere_id = ? AND periode_id = ?)
        ");
        $stmt_saisies->execute([$matiere_id, $periode_id]);
        $notes_saisies = $stmt_saisies->fetchColumn();
        
        if ($notes_saisies < $notes_attendues) {
            $_SESSION['error_message'] = "Validation impossible : il manque des notes. (" . $notes_saisies . "/" . $notes_attendues . " saisies)";
            // Redirect to the new validation page for better context
            header('Location: ' . APP_URL . "/index.php?page=prof_validation");
            exit;
        }

        // Mettre à jour la table de progression
        // UPSERT
        $stmt_check = $pdo->prepare("SELECT id FROM progression_saisie WHERE matiere_id = ? AND periode_id = ? AND professeur_id = ?");
        $stmt_check->execute([$matiere_id, $periode_id, $prof_id]);
        $exists = $stmt_check->fetch();
        
        if($exists) {
            $sql = "UPDATE progression_saisie SET valide_par_prof = TRUE, date_validation = NOW() WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$exists['id']]);
        } else {
            // total_etudiants and notes_saisies were not accurately filled before
            $sql = "INSERT INTO progression_saisie (matiere_id, periode_id, professeur_id, total_etudiants, total_notes_attendues, notes_saisies, pourcentage, valide_par_prof, date_validation)
                    VALUES (?, ?, ?, ?, ?, ?, 100, TRUE, NOW())";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$matiere_id, $periode_id, $prof_id, $nombre_etudiants, $notes_attendues, $notes_saisies]);
        }
        
        $_SESSION['success_message'] = "La saisie a été validée avec succès et est maintenant verrouillée.";

    } catch (Exception $e) {
        $_SESSION['error_message'] = "Erreur : " . $e->getMessage();
    }

    // Redirection
    header('Location: ' . APP_URL . "/index.php?page=prof_validation");
    exit;
}
