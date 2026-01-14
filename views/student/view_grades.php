<?php
$pdo = getDBConnection();
$etudiant_id = $_SESSION['user_id'];

// --- Single, optimized query to fetch all data at once ---
$stmt = $pdo->prepare("
    SELECT 
        p.id as periode_id,
        p.nom as periode_nom,
        m.id as matiere_id,
        m.nom as matiere_nom,
        m.coefficient,
        moy.moyenne,
        m.seuil_validation,
        cc.nom_colonne,
        n.valeur,
        n.statut
    FROM inscriptions_matieres im
    JOIN periodes p ON im.periode_id = p.id AND p.statut = 'publiee'
    JOIN matieres m ON im.matiere_id = m.id
    LEFT JOIN moyennes moy ON moy.etudiant_id = im.etudiant_id AND moy.matiere_id = im.matiere_id AND moy.periode_id = im.periode_id
    LEFT JOIN configuration_colonnes cc ON cc.matiere_id = im.matiere_id AND cc.periode_id = im.periode_id
    LEFT JOIN notes n ON n.colonne_id = cc.id AND n.etudiant_id = im.etudiant_id
    WHERE im.etudiant_id = ?
    ORDER BY p.date_publication DESC, m.nom ASC, cc.ordre ASC
");
$stmt->execute([$etudiant_id]);
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

// --- Process the flat results into a nested structure ---
$grades_by_period = [];
foreach ($results as $row) {
    $periode_nom = $row['periode_nom'];
    
    // Initialize period if not exists
    if (!isset($grades_by_period[$periode_nom])) {
        $grades_by_period[$periode_nom] = [];
    }
    
    $matiere_key = $row['matiere_id'];
    // Initialize subject if not exists
    if (!isset($grades_by_period[$periode_nom][$matiere_key])) {
        $grades_by_period[$periode_nom][$matiere_key] = [
            'matiere_nom' => $row['matiere_nom'],
            'notes' => [],
            'moyenne' => $row['moyenne'],
            'seuil_validation' => $row['seuil_validation'],
            'coefficient' => $row['coefficient']
        ];
    }
    
    // Add note detail if it exists (LEFT JOIN can result in NULLs)
    if ($row['nom_colonne']) {
        // Avoid duplicating notes if a student has no notes for a subject but has a grade
        $grades_by_period[$periode_nom][$matiere_key]['notes'][] = [
            'nom_colonne' => $row['nom_colonne'],
            'valeur' => $row['valeur'],
            'statut' => $row['statut']
        ];
    }
}
?>

<div class="page-container">
    <h2>Mes Notes</h2>

    <?php if (empty($grades_by_period)): ?>
        <div class="message info">Aucune note n'a été publiée pour le moment.</div>
    <?php else: ?>
        <?php foreach ($grades_by_period as $periode_nom => $matieres): ?>
            <div class="period-container">
                <h3><?php echo htmlspecialchars($periode_nom); ?></h3>
                <?php 
                $total_points = 0;
                $total_coeffs = 0;
                foreach ($matieres as $matiere): 
                    if (isset($matiere['moyenne']) && is_numeric($matiere['coefficient'])) {
                        $total_points += $matiere['moyenne'] * $matiere['coefficient'];
                        $total_coeffs += $matiere['coefficient'];
                    }
                ?>
                    <div class="subject-card">
                        <h4><?php echo htmlspecialchars($matiere['matiere_nom']); ?> (Coeff: <?php echo htmlspecialchars($matiere['coefficient']); ?>)</h4>
                        <div class="grades-grid">
                            <?php if (empty($matiere['notes'])): ?>
                                <p>Aucun détail de note disponible.</p>
                            <?php else: ?>
                                <?php foreach ($matiere['notes'] as $note): ?>
                                    <div class="grade-item">
                                        <span class="grade-label"><?php echo htmlspecialchars($note['nom_colonne']); ?>:</span>
                                        <span class="grade-value">
                                            <?php
                                            if ($note['statut'] && $note['statut'] !== 'saisie') {
                                                echo strtoupper(htmlspecialchars($note['statut']));
                                            } else if (isset($note['valeur'])) {
                                                echo htmlspecialchars(number_format($note['valeur'], 2, ',', ' '));
                                            } else {
                                                echo 'N/A';
                                            }
                                            ?>
                                        </span>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                        <div class="average-display">
                            Moyenne: <strong><?php echo isset($matiere['moyenne']) ? number_format($matiere['moyenne'], 2, ',', ' ') : 'N/A'; ?></strong>
                            <?php
                            $decision_text = 'EN ATTENTE';
                            if (isset($matiere['moyenne'])) {
                                $moyenne = $matiere['moyenne'];
                                $seuil = $matiere['seuil_validation'] ?? 10.0;
                                
                                if ($moyenne < 7) {
                                    $decision_text = 'NON VALIDÉ';
                                } elseif ($moyenne >= 7 && $moyenne < $seuil) {
                                    $decision_text = 'RATTRAPAGE';
                                } elseif ($moyenne >= $seuil) {
                                    $decision_text = 'VALIDÉ';
                                }
                            }
                            ?>
                            (<?php echo htmlspecialchars($decision_text); ?>)
                        </div>
                    </div>
                <?php endforeach; 

                $moyenne_generale = ($total_coeffs > 0) ? $total_points / $total_coeffs : null;
                $decision_generale_text = 'EN ATTENTE';
                if ($moyenne_generale !== null) {
                    if ($moyenne_generale < 7) {
                        $decision_generale_text = 'NON VALIDÉ';
                    } elseif ($moyenne_generale >= 7 && $moyenne_generale < 10) {
                        $decision_generale_text = 'RATTRAPAGE';
                    } elseif ($moyenne_generale >= 10) {
                        $decision_generale_text = 'VALIDÉ';
                    }
                }
                ?>
                <div class="overall-average-card">
                    <h4>Moyenne Générale (<?php echo htmlspecialchars($periode_nom); ?>)</h4>
                    <div class="average-display">
                        Moyenne: <strong><?php echo $moyenne_generale !== null ? number_format($moyenne_generale, 2, ',', ' ') : 'N/A'; ?></strong>
                        (<?php echo htmlspecialchars($decision_generale_text); ?>)
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>


