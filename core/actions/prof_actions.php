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

/**
 * Récupère et calcule les données statistiques pour un professeur donné.
 *
 * @param int $prof_id L'ID du professeur.
 * @return array Un tableau structuré des statistiques par matière et période.
 */
function get_professor_statistics_data(int $prof_id): array {
    $pdo = getDBConnection();
    // Include FormulaParser class
    require_once __DIR__ . '/../classes/FormulaParser.php';
    $parser = new FormulaParser();
    $statistics = [];

    // 1. Récupérer toutes les affectations du professeur
    $stmt_affectations = $pdo->prepare("
        SELECT ap.matiere_id, ap.periode_id, m.nom as matiere_nom, m.seuil_validation, p.nom as periode_nom, p.annee_universitaire, f.nom as filiere_nom
        FROM affectations_profs ap
        JOIN matieres m ON ap.matiere_id = m.id
        JOIN periodes p ON ap.periode_id = p.id
        JOIN filieres f ON m.filiere_id = f.id
        WHERE ap.professeur_id = ?
        ORDER BY p.annee_universitaire DESC, p.nom, m.nom
    ");
    $stmt_affectations->execute([$prof_id]);
    $affectations = $stmt_affectations->fetchAll(PDO::FETCH_ASSOC);

    foreach ($affectations as $affectation) {
        $matiere_id = $affectation['matiere_id'];
        $periode_id = $affectation['periode_id'];
        $matiere_nom = $affectation['matiere_nom'];
        $periode_nom = $affectation['periode_nom'];
        $filiere_nom = $affectation['filiere_nom'];
        $seuil_validation = $affectation['seuil_validation'];

        // Initialiser les données pour cette matière/période
        $subject_stats = [
            'matiere_info' => $affectation,
            'moyennes_etudiants' => [],
            'statistiques_globales' => [
                'moyenne_generale' => null,
                'mediane' => null, // Add median later
                'note_min' => null,
                'note_max' => null,
                'ecart_type' => null,
            ],
            'distribution_notes' => [],
        ];

        // 2. Récupérer la formule de calcul pour cette matière/période
        $stmt_formula = $pdo->prepare("SELECT formule FROM formules WHERE matiere_id = ? AND periode_id = ?");
        $stmt_formula->execute([$matiere_id, $periode_id]);
        $formula_string = $stmt_formula->fetchColumn();

        if (!$formula_string) {
            $subject_stats['error'] = "Aucune formule de calcul configurée pour cette matière/période.";
            $statistics[] = $subject_stats;
            continue;
        }

        // 3. Récupérer la configuration des colonnes pour les notes (pour FormulaParser)
        $stmt_cols_config = $pdo->prepare("SELECT id, code_colonne FROM configuration_colonnes WHERE matiere_id = ? AND periode_id = ?");
        $stmt_cols_config->execute([$matiere_id, $periode_id]);
        $colonnes_config = $stmt_cols_config->fetchAll(PDO::FETCH_KEY_PAIR); // [colonne_id => code_colonne]

        if (empty($colonnes_config)) {
            $subject_stats['error'] = "Aucune colonne configurée pour cette matière/période.";
            $statistics[] = $subject_stats;
            continue;
        }

        // 4. Récupérer tous les étudiants inscrits à cette matière/période
        $stmt_students = $pdo->prepare("
            SELECT im.etudiant_id, u.nom, u.prenom
            FROM inscriptions_matieres im
            JOIN utilisateurs u ON im.etudiant_id = u.id
            WHERE im.matiere_id = ? AND im.periode_id = ?
        ");
        $stmt_students->execute([$matiere_id, $periode_id]);
        $students = $stmt_students->fetchAll(PDO::FETCH_ASSOC);

        $student_averages = [];

        foreach ($students as $student) {
            $etudiant_id = $student['etudiant_id'];

            // 5. Récupérer toutes les notes de cet étudiant pour cette matière/période
            $stmt_grades = $pdo->prepare("
                SELECT n.valeur, cc.code_colonne, n.statut
                FROM notes n
                JOIN configuration_colonnes cc ON n.colonne_id = cc.id
                WHERE n.etudiant_id = ? AND cc.matiere_id = ? AND cc.periode_id = ?
            ");
            $stmt_grades->execute([$etudiant_id, $matiere_id, $periode_id]);
            $grades_raw = $stmt_grades->fetchAll(PDO::FETCH_ASSOC);

            $grade_values_for_parser = [];
            foreach ($colonnes_config as $col_id => $code_colonne) {
                $grade_values_for_parser[$code_colonne] = null; // Default to null if no note found
            }
            
            foreach ($grades_raw as $grade_entry) {
                if (in_array($grade_entry['statut'], ['absent', 'dispense', 'defaillant'])) {
                    $grade_values_for_parser[$grade_entry['code_colonne']] = 'ABS'; // Treat as ABS for parser
                } else {
                    $grade_values_for_parser[$grade_entry['code_colonne']] = $grade_entry['valeur'];
                }
            }

            try {
                $student_avg = $parser->evaluer($formula_string, $grade_values_for_parser);
                if ($student_avg !== null) {
                    $student_averages[] = $student_avg;
                    $subject_stats['moyennes_etudiants'][] = [
                        'etudiant_id' => $etudiant_id,
                        'nom' => $student['nom'],
                        'prenom' => $student['prenom'],
                        'moyenne' => round($student_avg, 2),
                        'valide' => ($student_avg >= $seuil_validation),
                    ];
                }
            } catch (Exception $e) {
                // Log error but don't stop processing other students
                error_log("Error calculating average for student $etudiant_id in matiere $matiere_id: " . $e->getMessage());
                // Optionally add an error message to subject_stats or student_stats
            }
        }

        // 6. Calculer les statistiques globales pour la matière
        if (!empty($student_averages)) {
            $subject_stats['statistiques_globales']['moyenne_generale'] = round(array_sum($student_averages) / count($student_averages), 2);
            $subject_stats['statistiques_globales']['note_min'] = min($student_averages);
            $subject_stats['statistiques_globales']['note_max'] = max($student_averages);

            // Calcul de la médiane
            sort($student_averages);
            $count = count($student_averages);
            $middle = floor($count / 2);
            $subject_stats['statistiques_globales']['mediane'] = ($count % 2 == 0) ? ($student_averages[$middle - 1] + $student_averages[$middle]) / 2 : $student_averages[$middle];
            $subject_stats['statistiques_globales']['mediane'] = round($subject_stats['statistiques_globales']['mediane'], 2);

            // Calcul de l'écart-type
            $sum_of_squares = 0;
            foreach ($student_averages as $avg) {
                $sum_of_squares += pow($avg - $subject_stats['statistiques_globales']['moyenne_generale'], 2);
            }
            $subject_stats['statistiques_globales']['ecart_type'] = round(sqrt($sum_of_squares / $count), 2);

            // Distribution des notes (par ex: 0-5, 5-10, 10-15, 15-20)
            $distribution_ranges = [
                '0-4.99' => 0, '5-9.99' => 0, '10-14.99' => 0, '15-20' => 0
            ];
            foreach ($student_averages as $avg) {
                if ($avg >= 0 && $avg < 5) $distribution_ranges['0-4.99']++;
                elseif ($avg >= 5 && $avg < 10) $distribution_ranges['5-9.99']++;
                elseif ($avg >= 10 && $avg < 15) $distribution_ranges['10-14.99']++;
                elseif ($avg >= 15 && $avg <= 20) $distribution_ranges['15-20']++;
            }
            $subject_stats['distribution_notes'] = $distribution_ranges;
        } else {
            $subject_stats['error'] = ($subject_stats['error'] ?? '') . " Aucune moyenne calculable pour cette matière/période.";
        }
        
        $statistics[] = $subject_stats;
    }

    return $statistics;
}