<?php
// Ce fichier gérera différentes actions de l'administrateur

/**
 * Gère l'ajout d'une nouvelle période de notation.
 */
function handle_add_period() {
    // --- 1. Validation des données ---
    $nom = trim($_POST['nom'] ?? '');
    $annee = trim($_POST['annee_universitaire'] ?? '');
    $type = trim($_POST['type'] ?? '');
    $date_debut = trim($_POST['date_debut_saisie'] ?? '');
    $date_fin = trim($_POST['date_fin_saisie'] ?? '');

    if (empty($nom) || empty($annee) || empty($type) || empty($date_debut) || empty($date_fin)) {
        $_SESSION['error_message'] = "Tous les champs sont obligatoires.";
        header('Location: ' . APP_URL . '/index.php?page=manage_periods');
        exit;
    }

    // Validation simple du format de l'année
    if (!preg_match('/^\d{4}-\d{4}$/', $annee)) {
        $_SESSION['error_message'] = "Le format de l'année universitaire doit être AAAA-AAAA (ex: 2024-2025).";
        header('Location: ' . APP_URL . '/index.php?page=manage_periods');
        exit;
    }

    // --- 2. Insertion dans la base de données ---
    try {
        $pdo = getDBConnection();
        $sql = "INSERT INTO periodes (nom, annee_universitaire, type, date_debut_saisie, date_fin_saisie) 
                VALUES (:nom, :annee, :type, :date_debut, :date_fin)";
        
        $stmt = $pdo->prepare($sql);
        
        $stmt->execute([
            ':nom' => $nom,
            ':annee' => $annee,
            ':type' => $type,
            ':date_debut' => $date_debut,
            ':date_fin' => $date_fin
        ]);

        $_SESSION['success_message'] = "La période '" . htmlspecialchars($nom) . "' a été ajoutée avec succès.";

    } catch (PDOException $e) {
        // Gérer les erreurs de BDD, notamment les codes uniques
        if ($e->errorInfo[1] == 1062) { // Code d'erreur pour entrée dupliquée
            $_SESSION['error_message'] = "Erreur : La période '" . htmlspecialchars($nom) . "' existe déjà.";
        } else {
            $_SESSION['error_message'] = "Une erreur de base de données est survenue. " . (DEBUG_MODE ? $e->getMessage() : "");
        }
    }

    // --- 3. Redirection ---
    header('Location: ' . APP_URL . '/index.php?page=manage_periods');
    exit;
}

/**
 * Gère l'ajout d'une nouvelle filière.
 */
function handle_add_filiere() {
    // Validation
    $nom = trim($_POST['nom'] ?? '');
    $niveau = trim($_POST['niveau'] ?? '');
    $responsable_id = trim($_POST['responsable_id'] ?? '');

    if (empty($nom) || empty($niveau)) {
        $_SESSION['error_message'] = "Les champs nom et niveau sont obligatoires.";
        header('Location: ' . APP_URL . '/index.php?page=manage_filieres');
        exit;
    }

    // Insertion BDD
    try {
        $pdo = getDBConnection();
        $sql = "INSERT INTO filieres (nom, niveau, responsable_id) VALUES (:nom, :niveau, :responsable_id)";
        $stmt = $pdo->prepare($sql);
        
        $stmt->execute([
            ':nom' => $nom,
            ':niveau' => $niveau,
            ':responsable_id' => !empty($responsable_id) ? $responsable_id : null
        ]);

        $_SESSION['success_message'] = "La filière '" . htmlspecialchars($nom) . "' a été ajoutée.";

    } catch (PDOException $e) {
        if ($e->errorInfo[1] == 1062) {
            $_SESSION['error_message'] = "Erreur : Le code de filière '" . htmlspecialchars($code) . "' existe déjà.";
        } else {
            $_SESSION['error_message'] = "Erreur BDD: " . (DEBUG_MODE ? $e->getMessage() : "Contactez un admin.");
        }
    }

    // Redirection
    header('Location: ' . APP_URL . '/index.php?page=manage_filieres');
    exit;
}

/**
 * Gère la mise à jour d'une filière existante.
 */
function handle_update_filiere() {
    // Validation
    $filiere_id = filter_input(INPUT_POST, 'filiere_id', FILTER_VALIDATE_INT);
    $nom = trim($_POST['nom'] ?? '');
    $niveau = trim($_POST['niveau'] ?? '');
    $responsable_id = trim($_POST['responsable_id'] ?? '');

    if (!$filiere_id || empty($nom) || empty($niveau)) {
        $_SESSION['error_message'] = "Les champs nom et niveau sont obligatoires.";
        header('Location: ' . APP_URL . '/index.php?page=edit_filiere&id=' . $filiere_id);
        exit;
    }

    // Mise à jour BDD
    try {
        $pdo = getDBConnection();
        $sql = "UPDATE filieres SET nom = :nom, niveau = :niveau, responsable_id = :responsable_id WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        
        $stmt->execute([
            ':nom' => $nom,
            ':niveau' => $niveau,
            ':responsable_id' => !empty($responsable_id) ? $responsable_id : null,
            ':id' => $filiere_id
        ]);

        $_SESSION['success_message'] = "La filière '" . htmlspecialchars($nom) . "' a été mise à jour avec succès.";

    } catch (PDOException $e) {
        if ($e->errorInfo[1] == 1062) {
            $_SESSION['error_message'] = "Erreur : Le code de filière '" . htmlspecialchars($code) . "' existe déjà.";
        } else {
            $_SESSION['error_message'] = "Erreur BDD: " . (DEBUG_MODE ? $e->getMessage() : "Contactez un admin.");
        }
    }

    // Redirection
    header('Location: ' . APP_URL . '/index.php?page=manage_filieres');
    exit;
}

/**
 * Gère la suppression d'une filière.
 */
function handle_delete_filiere() {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        $_SESSION['error_message'] = "Méthode non autorisée pour cette action.";
        header('Location: ' . APP_URL . '/index.php?page=manage_filieres');
        exit;
    }

    $filiere_id = filter_input(INPUT_POST, 'filiere_id', FILTER_VALIDATE_INT);

    if (!$filiere_id) {
        $_SESSION['error_message'] = "ID de filière non valide.";
        header('Location: ' . APP_URL . '/index.php?page=manage_filieres');
        exit;
    }

    try {
        $pdo = getDBConnection();
        $sql = "DELETE FROM filieres WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$filiere_id]);

        $_SESSION['success_message'] = "La filière a été supprimée avec succès.";

    } catch (PDOException $e) {
        if ($e->errorInfo[1] == 1451) { // Code d'erreur MySQL pour contrainte de clé étrangère
            $_SESSION['error_message'] = "Impossible de supprimer cette filière car elle est liée à d'autres enregistrements (ex: matières).";
        } else {
            $_SESSION['error_message'] = "Erreur BDD: " . (DEBUG_MODE ? $e->getMessage() : "Contactez un admin.");
        }
    }

    header('Location: ' . APP_URL . '/index.php?page=manage_filieres');
    exit;
}


/**
 * Gère l'ajout d'une nouvelle matière.
 */
function handle_add_subject() {
    // Validation
    $nom = trim($_POST['nom'] ?? '');
    $filiere_id = trim($_POST['filiere_id'] ?? '');
    $coefficient = trim($_POST['coefficient'] ?? '1');
    $seuil_validation = trim($_POST['seuil_validation'] ?? '10');

    if (empty($nom) || empty($filiere_id)) {
        $_SESSION['error_message'] = "Les champs nom et filière sont obligatoires.";
        header('Location: ' . APP_URL . '/index.php?page=manage_subjects');
        exit;
    }

    // Insertion BDD
    try {
        $pdo = getDBConnection();
        $sql = "INSERT INTO matieres (nom, filiere_id, coefficient, seuil_validation) 
                VALUES (:nom, :filiere_id, :coefficient, :seuil_validation)";
        $stmt = $pdo->prepare($sql);
        
        $stmt->execute([
            ':nom' => $nom,
            ':filiere_id' => $filiere_id,
            ':coefficient' => $coefficient,
            ':seuil_validation' => $seuil_validation
        ]);

        $_SESSION['success_message'] = "La matière '" . htmlspecialchars($nom) . "' a été ajoutée.";

    } catch (PDOException $e) {
        if ($e->errorInfo[1] == 1062) {
            $_SESSION['error_message'] = "Erreur : La matière '" . htmlspecialchars($nom) . "' existe déjà pour cette filière.";
        } else {
            $_SESSION['error_message'] = "Erreur BDD: " . (DEBUG_MODE ? $e->getMessage() : "Contactez un admin.");
        }
    }

    // Redirection
    header('Location: ' . APP_URL . '/index.php?page=manage_subjects');
    exit;
}

/**
 * Gère la mise à jour d'une matière existante.
 */
function handle_update_subject() {
    // Validation
    $matiere_id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
    $nom = trim($_POST['nom'] ?? '');
    $filiere_id = trim($_POST['filiere_id'] ?? '');
    $coefficient = trim($_POST['coefficient'] ?? '1');
    $seuil_validation = trim($_POST['seuil_validation'] ?? '10');

    if (!$matiere_id || empty($nom) || empty($filiere_id)) {
        $_SESSION['error_message'] = "Les champs nom et filière sont obligatoires.";
        header('Location: ' . APP_URL . '/index.php?page=edit_subject&id=' . $matiere_id);
        exit;
    }

    // Mise à jour BDD
    try {
        $pdo = getDBConnection();
        $sql = "UPDATE matieres SET nom = :nom, filiere_id = :filiere_id, coefficient = :coefficient, seuil_validation = :seuil_validation WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        
        $stmt->execute([
            ':nom' => $nom,
            ':filiere_id' => $filiere_id,
            ':coefficient' => $coefficient,
            ':seuil_validation' => $seuil_validation,
            ':id' => $matiere_id
        ]);

        $_SESSION['success_message'] = "La matière '" . htmlspecialchars($nom) . "' a été mise à jour avec succès.";

    } catch (PDOException $e) {
        if ($e->errorInfo[1] == 1062) {
            $_SESSION['error_message'] = "Erreur : La matière '" . htmlspecialchars($nom) . "' existe déjà pour cette filière.";
        } else {
            $_SESSION['error_message'] = "Erreur BDD: " . (DEBUG_MODE ? $e->getMessage() : "Contactez un admin.");
        }
    }

    // Redirection
    header('Location: ' . APP_URL . '/index.php?page=manage_subjects');
    exit;
}

/**
 * Gère la suppression d'une matière.
 */
function handle_delete_subject() {
    $matiere_id = filter_input(INPUT_POST, 'matiere_id', FILTER_VALIDATE_INT);

    if (!$matiere_id) {
        $_SESSION['error_message'] = "ID de matière non valide.";
        header('Location: ' . APP_URL . '/index.php?page=manage_subjects');
        exit;
    }

    try {
        $pdo = getDBConnection();
        $sql = "DELETE FROM matieres WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$matiere_id]);

        $_SESSION['success_message'] = "La matière a été supprimée avec succès.";

    } catch (PDOException $e) {
        // Gérer les contraintes de clé étrangère
        if ($e->errorInfo[1] == 1451) { // Code d'erreur MySQL pour contrainte de clé étrangère
            $_SESSION['error_message'] = "Impossible de supprimer cette matière car elle est liée à d'autres enregistrements (ex: notes, inscriptions).";
        } else {
            $_SESSION['error_message'] = "Erreur BDD: " . (DEBUG_MODE ? $e->getMessage() : "Contactez un admin.");
        }
    }

    header('Location: ' . APP_URL . '/index.php?page=manage_subjects');
    exit;
}


/**
 * Gère l'ajout d'une nouvelle colonne de note.
 */
function handle_add_column() {
    // Validation
    $periode_id = filter_input(INPUT_POST, 'periode_id', FILTER_VALIDATE_INT);
    $matiere_id = filter_input(INPUT_POST, 'matiere_id', FILTER_VALIDATE_INT);
    $nom_colonne = trim($_POST['nom_colonne'] ?? '');
    $code_colonne = trim($_POST['code_colonne'] ?? '');
    $type = trim($_POST['type'] ?? '');
    $note_max = trim($_POST['note_max'] ?? '');
    $coefficient = trim($_POST['coefficient'] ?? '');

    $redirect_url = APP_URL . "/index.php?page=configure_notes&periode_id=$periode_id&matiere_id=$matiere_id";

    if (!$periode_id || !$matiere_id || empty($nom_colonne) || empty($code_colonne) || empty($type) || !is_numeric($note_max) || !is_numeric($coefficient)) {
        $_SESSION['error_message'] = "Tous les champs sont obligatoires et doivent être corrects.";
        header('Location: ' . $redirect_url);
        exit;
    }

    // Insertion BDD
    try {
        $pdo = getDBConnection();

        // Déterminer l'ordre
        $stmt_ordre = $pdo->prepare("SELECT COUNT(*) as count FROM configuration_colonnes WHERE periode_id = ? AND matiere_id = ?");
        $stmt_ordre->execute([$periode_id, $matiere_id]);
        $ordre = $stmt_ordre->fetch()['count'] + 1;

        $sql = "INSERT INTO configuration_colonnes (matiere_id, periode_id, nom_colonne, code_colonne, type, note_max, coefficient, ordre)
                VALUES (:matiere_id, :periode_id, :nom_colonne, :code_colonne, :type, :note_max, :coefficient, :ordre)";
        $stmt = $pdo->prepare($sql);
        
        $stmt->execute([
            ':matiere_id' => $matiere_id,
            ':periode_id' => $periode_id,
            ':nom_colonne' => $nom_colonne,
            ':code_colonne' => $code_colonne,
            ':type' => $type,
            ':note_max' => $note_max,
            ':coefficient' => $coefficient,
            ':ordre' => $ordre
        ]);

        $_SESSION['success_message'] = "La colonne '" . htmlspecialchars($nom_colonne) . "' a été ajoutée.";

    } catch (PDOException $e) {
        if ($e->errorInfo[1] == 1062) {
            $_SESSION['error_message'] = "Erreur : Le code de colonne '" . htmlspecialchars($code_colonne) . "' existe déjà pour cette matière et période.";
        } else {
            $_SESSION['error_message'] = "Erreur BDD: " . (DEBUG_MODE ? $e->getMessage() : "Contactez un admin.");
        }
    }

    // Redirection
    header('Location: ' . $redirect_url);
    exit;
}

/**
 * Gère la mise à jour d'une colonne de note.
 */
function handle_update_column() {
    // Validation
    $column_id = filter_input(INPUT_POST, 'column_id', FILTER_VALIDATE_INT);
    $periode_id = filter_input(INPUT_POST, 'periode_id', FILTER_VALIDATE_INT);
    $matiere_id = filter_input(INPUT_POST, 'matiere_id', FILTER_VALIDATE_INT);
    $nom_colonne = trim($_POST['nom_colonne'] ?? '');
    $code_colonne = trim($_POST['code_colonne'] ?? '');
    $type = trim($_POST['type'] ?? '');
    $note_max = trim($_POST['note_max'] ?? '');
    $coefficient = trim($_POST['coefficient'] ?? '');
    $obligatoire = filter_input(INPUT_POST, 'obligatoire', FILTER_VALIDATE_INT);

    $redirect_url = APP_URL . "/index.php?page=configure_notes&periode_id=$periode_id&matiere_id=$matiere_id";

    if (!$column_id || !$periode_id || !$matiere_id || empty($nom_colonne) || empty($code_colonne) || empty($type) || !is_numeric($note_max) || !is_numeric($coefficient) || !isset($obligatoire)) {
        $_SESSION['error_message'] = "Tous les champs sont obligatoires et doivent être corrects.";
        header('Location: ' . APP_URL . "/index.php?page=edit_column&id=$column_id");
        exit;
    }

    // Mise à jour BDD
    try {
        $pdo = getDBConnection();
        $sql = "UPDATE configuration_colonnes 
                SET nom_colonne = :nom, code_colonne = :code, type = :type, note_max = :max, coefficient = :coeff, obligatoire = :obli
                WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        
        $stmt->execute([
            ':nom' => $nom_colonne,
            ':code' => $code_colonne,
            ':type' => $type,
            ':max' => $note_max,
            ':coeff' => $coefficient,
            ':obli' => $obligatoire,
            ':id' => $column_id
        ]);

        $_SESSION['success_message'] = "La colonne '" . htmlspecialchars($nom_colonne) . "' a été mise à jour.";

    } catch (PDOException $e) {
        if ($e->errorInfo[1] == 1062) {
            $_SESSION['error_message'] = "Erreur : Le code de colonne '" . htmlspecialchars($code_colonne) . "' existe déjà pour cette matière et période.";
        } else {
            $_SESSION['error_message'] = "Erreur BDD: " . (DEBUG_MODE ? $e->getMessage() : "Contactez un admin.");
        }
        header('Location: ' . APP_URL . "/index.php?page=edit_column&id=$column_id");
        exit;
    }

    // Redirection
    header('Location: ' . $redirect_url);
    exit;
}

/**
 * Gère la suppression d'une colonne de note.
 */
function handle_delete_column() {
    // Validation
    $column_id = filter_input(INPUT_POST, 'column_id', FILTER_VALIDATE_INT);
    $periode_id = filter_input(INPUT_POST, 'periode_id', FILTER_VALIDATE_INT);
    $matiere_id = filter_input(INPUT_POST, 'matiere_id', FILTER_VALIDATE_INT);

    $redirect_url = APP_URL . "/index.php?page=configure_notes&periode_id=$periode_id&matiere_id=$matiere_id";

    if (!$column_id || !$periode_id || !$matiere_id) {
        $_SESSION['error_message'] = "Informations manquantes pour la suppression.";
        header('Location: ' . APP_URL . '/index.php?page=configure_notes');
        exit;
    }

    // Suppression BDD
    try {
        $pdo = getDBConnection();
        
        // Sécurité : Vérifier si des notes existent pour cette colonne
        $stmt_check = $pdo->prepare("SELECT COUNT(*) FROM notes WHERE colonne_id = ?");
        $stmt_check->execute([$column_id]);
        if ($stmt_check->fetchColumn() > 0) {
            throw new Exception("Impossible de supprimer cette colonne car des notes y ont déjà été saisies.");
        }

        $sql = "DELETE FROM configuration_colonnes WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$column_id]);

        $_SESSION['success_message'] = "La colonne a été supprimée avec succès.";

    } catch (Exception $e) {
        $_SESSION['error_message'] = "Erreur de suppression : " . $e->getMessage();
    }

    // Redirection
    header('Location: ' . $redirect_url);
    exit;
}

/**
 * Gère la sauvegarde d'une formule de calcul.
 */
function handle_save_formula() {
    // Validation
    $periode_id = filter_input(INPUT_POST, 'periode_id', FILTER_VALIDATE_INT);
    $matiere_id = filter_input(INPUT_POST, 'matiere_id', FILTER_VALIDATE_INT);
    $formule = trim($_POST['formule'] ?? '');

    $redirect_url = APP_URL . "/index.php?page=manage_formulas&periode_id=$periode_id&matiere_id=$matiere_id";

    if (!$periode_id || !$matiere_id) {
        // Ne devrait pas arriver si le formulaire est bien rempli
        header('Location: ' . APP_URL . '/index.php?page=manage_formulas');
        exit;
    }

    // Upsert (Update or Insert)
    try {
        $pdo = getDBConnection();

        // Vérifier si une formule existe déjà
        $stmt_check = $pdo->prepare("SELECT id FROM formules WHERE periode_id = ? AND matiere_id = ?");
        $stmt_check->execute([$periode_id, $matiere_id]);
        $exists = $stmt_check->fetch();

        if ($exists) {
            // Update
            $sql = "UPDATE formules SET formule = :formule WHERE id = :id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([':formule' => $formule, ':id' => $exists['id']]);
        } else {
            // Insert
            $sql = "INSERT INTO formules (periode_id, matiere_id, formule) VALUES (:periode_id, :matiere_id, :formule)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([':periode_id' => $periode_id, ':matiere_id' => $matiere_id, ':formule' => $formule]);
        }

        $_SESSION['success_message'] = "La formule a été enregistrée avec succès.";

    } catch (PDOException $e) {
        $_SESSION['error_message'] = "Erreur BDD: " . (DEBUG_MODE ? $e->getMessage() : "Contactez un admin.");
    }

    // Redirection
    header('Location: ' . $redirect_url);
    exit;
}

/**
 * Gère l'affectation d'un professeur à une matière.
 */
function handle_add_assignment() {
    // Validation
    $professeur_id = filter_input(INPUT_POST, 'professeur_id', FILTER_VALIDATE_INT);
    $matiere_id = filter_input(INPUT_POST, 'matiere_id', FILTER_VALIDATE_INT);
    $periode_id = filter_input(INPUT_POST, 'periode_id', FILTER_VALIDATE_INT);
    $groupe = trim($_POST['groupe'] ?? '');

    if (!$professeur_id || !$matiere_id || !$periode_id) {
        $_SESSION['error_message'] = "Les champs professeur, matière et période sont obligatoires.";
        header('Location: ' . APP_URL . '/index.php?page=manage_assignments');
        exit;
    }

    // Insertion BDD
    try {
        $pdo = getDBConnection();
        $sql = "INSERT INTO affectations_profs (professeur_id, matiere_id, periode_id, groupe) 
                VALUES (:prof_id, :mat_id, :per_id, :groupe)";
        $stmt = $pdo->prepare($sql);
        
        $stmt->execute([
            ':prof_id' => $professeur_id,
            ':mat_id' => $matiere_id,
            ':per_id' => $periode_id,
            ':groupe' => !empty($groupe) ? $groupe : null
        ]);

        $_SESSION['success_message'] = "L'affectation a été créée avec succès.";

    } catch (PDOException $e) {
        if ($e->errorInfo[1] == 1062) {
            $_SESSION['error_message'] = "Erreur : Cette affectation (professeur, matière, période, groupe) existe déjà.";
        } else {
            $_SESSION['error_message'] = "Erreur BDD: " . (DEBUG_MODE ? $e->getMessage() : "Contactez un admin.");
        }
    }

    // Redirection
    header('Location: ' . APP_URL . '/index.php?page=manage_assignments');
    exit;
}

/**
 * Gère l'inscription d'un étudiant à une matière.
 */
function handle_add_enrollment() {
    // Validation
    $etudiant_id = filter_input(INPUT_POST, 'etudiant_id', FILTER_VALIDATE_INT);
    $matiere_id = filter_input(INPUT_POST, 'matiere_id', FILTER_VALIDATE_INT);
    $periode_id = filter_input(INPUT_POST, 'periode_id', FILTER_VALIDATE_INT);
    $groupe = trim($_POST['groupe'] ?? '');

    if (!$etudiant_id || !$matiere_id || !$periode_id) {
        $_SESSION['error_message'] = "Les champs étudiant, matière et période sont obligatoires.";
        header('Location: ' . APP_URL . '/index.php?page=manage_enrollments');
        exit;
    }

    // Insertion BDD
    try {
        $pdo = getDBConnection();
        $sql = "INSERT INTO inscriptions_matieres (etudiant_id, matiere_id, periode_id, groupe) 
                VALUES (:etudiant_id, :mat_id, :per_id, :groupe)";
        $stmt = $pdo->prepare($sql);
        
        $stmt->execute([
            ':etudiant_id' => $etudiant_id,
            ':mat_id' => $matiere_id,
            ':per_id' => $periode_id,
            ':groupe' => !empty($groupe) ? $groupe : null
        ]);

        $_SESSION['success_message'] = "L'inscription a été créée avec succès.";

    } catch (PDOException $e) {
        if ($e->errorInfo[1] == 1062) {
            $_SESSION['error_message'] = "Erreur : Cet étudiant est déjà inscrit à cette matière pour cette période.";
        } else {
            $_SESSION['error_message'] = "Erreur BDD: " . (DEBUG_MODE ? $e->getMessage() : "Contactez un admin.");
        }
    }

    // Redirection
    header('Location: ' . APP_URL . '/index.php?page=manage_enrollments');
    exit;
}

/**
 * Gère l'ajout d'un nouvel utilisateur.
 */
function handle_add_user() {
    // Validation
    $nom = trim($_POST['nom'] ?? '');
    $prenom = trim($_POST['prenom'] ?? '');
    $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
    $mot_de_passe = $_POST['mot_de_passe'] ?? '';
    $role = trim($_POST['role'] ?? '');

    if (empty($nom) || empty($prenom) || !$email || empty($mot_de_passe) || empty($role)) {
        $_SESSION['error_message'] = "Tous les champs sont obligatoires et l'email doit être valide.";
        header('Location: ' . APP_URL . '/index.php?page=manage_users');
        exit;
    }

    // Hashage du mot de passe
    $hashed_password = password_hash($mot_de_passe, PASSWORD_DEFAULT);

    // Insertion BDD
    try {
        $pdo = getDBConnection();
        $sql = "INSERT INTO utilisateurs (nom, prenom, email, mot_de_passe, role) 
                VALUES (:nom, :prenom, :email, :mot_de_passe, :role)"; 
        $stmt = $pdo->prepare($sql);
        
        $stmt->execute([
            ':nom' => $nom,
            ':prenom' => $prenom,
            ':email' => $email,
            ':mot_de_passe' => $hashed_password,
            ':role' => $role
        ]);

        $_SESSION['success_message'] = "L'utilisateur '" . htmlspecialchars($prenom . ' ' . $nom) . "' a été ajouté.";

    } catch (PDOException $e) {
        if ($e->errorInfo[1] == 1062) {
            $_SESSION['error_message'] = "Erreur : L'adresse email '" . htmlspecialchars($email) . "' est déjà utilisée.";
        } else {
            $_SESSION['error_message'] = "Erreur BDD: " . (DEBUG_MODE ? $e->getMessage() : "Contactez un admin.");
        }
    }

    // Redirection
    header('Location: ' . APP_URL . '/index.php?page=manage_users');
    exit;
}

/**
 * Gère la mise à jour d'un utilisateur existant.
 */
function handle_update_user() {
    // Validation
    $user_id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
    $nom = trim($_POST['nom'] ?? '');
    $prenom = trim($_POST['prenom'] ?? '');
    $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
    $role = trim($_POST['role'] ?? '');

    if (!$user_id || empty($nom) || empty($prenom) || !$email || empty($role)) {
        $_SESSION['error_message'] = "Tous les champs sont obligatoires et l'email doit être valide.";
        header('Location: ' . APP_URL . '/index.php?page=edit_user&id=' . $user_id);
        exit;
    }

    // Mise à jour BDD
    try {
        $pdo = getDBConnection();
        $sql = "UPDATE utilisateurs SET nom = :nom, prenom = :prenom, email = :email, role = :role WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        
        $stmt->execute([
            ':nom' => $nom,
            ':prenom' => $prenom,
            ':email' => $email,
            ':role' => $role,
            ':id' => $user_id
        ]);

        $_SESSION['success_message'] = "L'utilisateur '" . htmlspecialchars($prenom . ' ' . $nom) . "' a été mis à jour avec succès.";

    } catch (PDOException $e) {
        if ($e->errorInfo[1] == 1062) {
            $_SESSION['error_message'] = "Erreur : L'adresse email '" . htmlspecialchars($email) . "' est déjà utilisée.";
        } else {
            $_SESSION['error_message'] = "Erreur BDD: " . (DEBUG_MODE ? $e->getMessage() : "Contactez un admin.");
        }
    }

    // Redirection
    header('Location: ' . APP_URL . '/index.php?page=manage_users');
    exit;
}
    
/**
 * Gère la suppression d'un utilisateur.
 */
function handle_delete_user() {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        $_SESSION['error_message'] = "Méthode non autorisée pour cette action.";
        header('Location: ' . APP_URL . '/index.php?page=manage_users');
        exit;
    }

    $user_id = filter_input(INPUT_POST, 'user_id', FILTER_VALIDATE_INT);

    if (!$user_id) {
        $_SESSION['error_message'] = "ID utilisateur non valide.";
        header('Location: ' . APP_URL . '/index.php?page=manage_users');
        exit;
    }

    try {
        $pdo = getDBConnection();

        // Vérifier si l'utilisateur à supprimer est l'utilisateur actuellement connecté
        if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $user_id) {
            $_SESSION['error_message'] = "Vous ne pouvez pas supprimer votre propre compte en étant connecté.";
            header('Location: ' . APP_URL . '/index.php?page=manage_users');
            exit;
        }

        // Vérifier si l'utilisateur est lié à d'autres tables (matières, inscriptions, etc.)
        // Si oui, la suppression en cascade ou le rejet est géré par la base de données.
        // Ici, nous allons juste essayer de supprimer et gérer l'erreur si une contrainte est violée.

        $sql = "DELETE FROM utilisateurs WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$user_id]);

        $_SESSION['success_message'] = "L'utilisateur a été supprimé avec succès.";

    } catch (PDOException $e) {
        // Gérer les contraintes de clé étrangère
        if ($e->errorInfo[1] == 1451) { // Code d'erreur MySQL pour contrainte de clé étrangère
            $_SESSION['error_message'] = "Impossible de supprimer cet utilisateur car il est lié à d'autres enregistrements (ex: notes, matières, etc.).";
        } else {
            $_SESSION['error_message'] = "Erreur BDD: " . (DEBUG_MODE ? $e->getMessage() : "Contactez un admin.");
        }
    }

    header('Location: ' . APP_URL . '/index.php?page=manage_users');
    exit;
}

/**
 * Gère la mise à jour d'une période.
 */
function handle_update_period() {
    // Validation
    $periode_id = filter_input(INPUT_POST, 'periode_id', FILTER_VALIDATE_INT);
    $nom = trim($_POST['nom'] ?? '');
    $annee = trim($_POST['annee_universitaire'] ?? '');
    $type = trim($_POST['type'] ?? '');
    $date_debut = trim($_POST['date_debut_saisie'] ?? '');
    $date_fin = trim($_POST['date_fin_saisie'] ?? '');

    if (!$periode_id || empty($nom) || empty($annee) || empty($type) || empty($date_debut) || empty($date_fin)) {
        $_SESSION['error_message'] = "Tous les champs sont obligatoires.";
        header('Location: ' . APP_URL . '/index.php?page=edit_period&id=' . $periode_id);
        exit;
    }

    // Mise à jour BDD
    try {
        $pdo = getDBConnection();
        $sql = "UPDATE periodes SET nom = ?, annee_universitaire = ?, type = ?, date_debut_saisie = ?, date_fin_saisie = ? WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$nom, $annee, $type, $date_debut, $date_fin, $periode_id]);

        $_SESSION['success_message'] = "La période a été mise à jour avec succès.";
        header('Location: ' . APP_URL . '/index.php?page=manage_periods');
        exit;

    } catch (PDOException $e) {
        $_SESSION['error_message'] = "Erreur BDD: " . (DEBUG_MODE ? $e->getMessage() : "Contactez un admin.");
        header('Location: ' . APP_URL . '/index.php?page=edit_period&id=' . $periode_id);
        exit;
    }
}

/**
 * Gère la suppression d'une période.
 */
function handle_delete_period() {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        $_SESSION['error_message'] = "Méthode non autorisée pour cette action.";
        header('Location: ' . APP_URL . '/index.php?page=manage_periods');
        exit;
    }

    $periode_id = filter_input(INPUT_POST, 'periode_id', FILTER_VALIDATE_INT);

    if (!$periode_id) {
        $_SESSION['error_message'] = "ID de période non valide.";
        header('Location: ' . APP_URL . '/index.php?page=manage_periods');
        exit;
    }

    try {
        $pdo = getDBConnection();
        $sql = "DELETE FROM periodes WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$periode_id]);

        $_SESSION['success_message'] = "La période a été supprimée avec succès.";

    } catch (PDOException $e) {
        $_SESSION['error_message'] = "Erreur BDD: " . (DEBUG_MODE ? $e->getMessage() : "Contactez un admin.");
    }

    header('Location: ' . APP_URL . '/index.php?page=manage_periods');
    exit;
}

/**
 * Gère le changement de statut d'une période.
 */
function handle_change_period_status() {
    $periode_id = filter_input(INPUT_POST, 'periode_id', FILTER_VALIDATE_INT);
    $statut = trim($_POST['statut'] ?? '');

    if (!$periode_id || !in_array($statut, ['a_venir', 'ouverte', 'fermee', 'publiee'])) {
        $_SESSION['error_message'] = "Données non valides.";
        header('Location: ' . APP_URL . '/index.php?page=manage_periods');
        exit;
    }

    try {
        $pdo = getDBConnection();
        $sql = "UPDATE periodes SET statut = ? WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$statut, $periode_id]);

        $_SESSION['success_message'] = "Le statut de la période a été mis à jour.";

    } catch (PDOException $e) {
        $_SESSION['error_message'] = "Erreur BDD: " . (DEBUG_MODE ? $e->getMessage() : "Contactez un admin.");
    }

    header('Location: ' . APP_URL . '/index.php?page=manage_periods');
    exit;
}

/**
 * Calcule les moyennes pour une matière et une période données.
 */
function handle_calculate_averages() {
    // Validation
    $periode_id = filter_input(INPUT_POST, 'periode_id', FILTER_VALIDATE_INT);
    $matiere_id = filter_input(INPUT_POST, 'matiere_id', FILTER_VALIDATE_INT);

    $redirect_url = APP_URL . "/index.php?page=manage_formulas&periode_id=$periode_id&matiere_id=$matiere_id";

    if (!$periode_id || !$matiere_id) {
        $_SESSION['error_message'] = "Les champs période et matière sont obligatoires pour le calcul des moyennes.";
        header('Location: ' . $redirect_url);
        exit;
    }

    try {
        $pdo = getDBConnection();
        require_once '../core/classes/FormulaParser.php';
        $parser = new FormulaParser();

        // 1. Récupérer la formule
        $stmt_formule = $pdo->prepare("SELECT formule FROM formules WHERE matiere_id = ? AND periode_id = ?");
        $stmt_formule->execute([$matiere_id, $periode_id]);
        $formule = $stmt_formule->fetchColumn();

        if (!$formule) {
            throw new Exception("Aucune formule n'est définie pour cette matière pour la période sélectionnée.");
        }
        
        // 2. Récupérer les codes de colonnes pour cette matière/période
        $stmt_cols = $pdo->prepare("SELECT code_colonne FROM configuration_colonnes WHERE matiere_id = ? AND periode_id = ?");
        $stmt_cols->execute([$matiere_id, $periode_id]);
        $codes_colonnes = $stmt_cols->fetchAll(PDO::FETCH_COLUMN);

        // 3. Récupérer les étudiants inscrits
        $stmt_etudiants = $pdo->prepare("SELECT etudiant_id FROM inscriptions_matieres WHERE matiere_id = ? AND periode_id = ?");
        $stmt_etudiants->execute([$matiere_id, $periode_id]);
        $etudiant_ids = $stmt_etudiants->fetchAll(PDO::FETCH_COLUMN);

        // 4. Pour chaque étudiant, calculer et stocker la moyenne
        foreach ($etudiant_ids as $etudiant_id) {
            // Récupérer les notes de l'étudiant avec leur statut
            $stmt_notes = $pdo->prepare("
                SELECT cc.code_colonne, n.valeur, n.statut
                FROM notes n
                JOIN configuration_colonnes cc ON n.colonne_id = cc.id
                WHERE n.etudiant_id = ? AND cc.matiere_id = ? AND cc.periode_id = ?
            ");
            $stmt_notes->execute([$etudiant_id, $matiere_id, $periode_id]);
            $notes_raw = $stmt_notes->fetchAll(PDO::FETCH_ASSOC);
            
            // Préparer le tableau de notes complet pour le parser
            $notes_for_parser = array_fill_keys($codes_colonnes, null);
            foreach ($notes_raw as $note) {
                if ($note['statut'] === 'saisie') {
                    $notes_for_parser[$note['code_colonne']] = $note['valeur'];
                } else {
                    // Pour 'absent', 'dispense', etc., passer un code que le parser interprète comme NULL
                    $notes_for_parser[$note['code_colonne']] = strtoupper($note['statut']); 
                }
            }

            // Calculer la moyenne
            $moyenne = $parser->evaluer($formule, $notes_for_parser); 

            // Upsert dans la table des moyennes
            $stmt_check = $pdo->prepare("SELECT id FROM moyennes WHERE etudiant_id = ? AND matiere_id = ? AND periode_id = ?");
            $stmt_check->execute([$etudiant_id, $matiere_id, $periode_id]);
            $exists = $stmt_check->fetch();

            if ($exists) {
                $sql = "UPDATE moyennes SET moyenne = ?, statut_validation = 'non_validée' WHERE id = ?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$moyenne, $exists['id']]);
            } else {
                $sql = "INSERT INTO moyennes (etudiant_id, matiere_id, periode_id, moyenne, statut_validation) VALUES (?, ?, ?, ?, 'non_validée')";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$etudiant_id, $matiere_id, $periode_id, $moyenne]);
            }
        }

        $_SESSION['success_message'] = "Le calcul des moyennes a été effectué avec succès.";

    } catch (Exception $e) {
        $_SESSION['error_message'] = "Erreur lors du calcul : " . $e->getMessage();
        error_log("Formula Calculation Error: " . $e->getMessage());
    }

    // Redirection
    header('Location: ' . $redirect_url);
    exit;
}

/**
 * Gère le déverrouillage de la saisie des notes pour un professeur.
 */
function handle_unlock_grades() {
    // Seul un admin peut faire ça, déjà protégé par le routeur principal.
    $progression_id = filter_input(INPUT_POST, 'progression_id', FILTER_VALIDATE_INT);

    if (!$progression_id) {
        $_SESSION['error_message'] = "ID de progression non valide.";
        header('Location: ' . APP_URL . '/index.php?page=view_progress');
        exit;
    }

    try {
        $pdo = getDBConnection();
        $sql = "UPDATE progression_saisie SET valide_par_prof = FALSE, date_validation = NULL WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$progression_id]);

        $_SESSION['success_message'] = "La saisie a été déverrouillée avec succès. Le professeur peut à nouveau modifier les notes.";

    } catch (PDOException $e) {
        $_SESSION['error_message'] = "Erreur BDD lors du déverrouillage : " . (DEBUG_MODE ? $e->getMessage() : "Contactez un admin.");
    }

    header('Location: ' . APP_URL . '/index.php?page=view_progress');
    exit;
}
