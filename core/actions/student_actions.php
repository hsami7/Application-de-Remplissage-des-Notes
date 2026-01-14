<?php
require_once __DIR__ . '/../lib/fpdf/fpdf.php';

/**
 * Custom PDF class to create header and footer for the transcript.
 */
class TranscriptPDF extends FPDF
{
    private $studentName;
    private $schoolYear;

    function __construct($studentName = '', $schoolYear = '', $orientation = 'P', $unit = 'mm', $size = 'A4') {
        parent::__construct($orientation, $unit, $size);
        $this->studentName = $studentName;
        $this->schoolYear = $schoolYear;
    }

    // Page header
    function Header()
    {
        // Logo (optional)
        // $this->Image('path/to/logo.png', 10, 6, 30);
        
        $this->SetFont('Helvetica', 'B', 16);
        $this->Cell(0, 10, mb_convert_encoding('Relevé de Notes', 'ISO-8859-1', 'UTF-8'), 0, 1, 'C');
        $this->SetFont('Helvetica', '', 12);
        $this->Cell(0, 10, mb_convert_encoding('Année Universitaire: ' . $this->schoolYear, 'ISO-8859-1', 'UTF-8'), 0, 1, 'C');
        $this->Ln(5);

        $this->SetFont('Helvetica', 'B', 12);
        $this->Cell(0, 10, mb_convert_encoding('Étudiant: ' . $this->studentName, 'ISO-8859-1', 'UTF-8'), 0, 1, 'L');
        $this->Ln(10);
    }

    // Page footer
    function Footer()
    {
        $this->SetY(-15);
        $this->SetFont('Helvetica', 'I', 8);
        $this->Cell(0, 10, 'Page ' . $this->PageNo() . '/{nb}', 0, 0, 'C');
        $this->SetX(-40);
        $this->Cell(0, 10, 'Date: ' . date('d/m/Y'), 0, 0, 'R');
    }

    // Chapter title
    function ChapterTitle($label)
    {
        $this->SetFont('Helvetica', 'B', 14);
        $this->SetFillColor(200, 220, 255);
        $this->Cell(0, 8, mb_convert_encoding($label, 'ISO-8859-1', 'UTF-8'), 0, 1, 'L', true);
        $this->Ln(4);
    }
    
    // Subject table
    function SubjectTable($header, $data)
    {
        // Column widths - Adjusted for 3 columns (Matiere, Note / 20, Statut Validation)
        $w = array(90, 40, 60); 
        // Header
        $this->SetFont('Helvetica', 'B', 10);
        for($i=0; $i<count($header); $i++)
            $this->Cell($w[$i], 7, $header[$i], 1, 0, 'C');
        $this->Ln();
        // Data
        $this->SetFont('Helvetica', '', 10);
        foreach($data as $row)
        {
            $this->Cell($w[0], 6, $row[0], 'LR');
            $this->Cell($w[1], 6, $row[1], 'LR', 0, 'C');
            $this->Cell($w[2], 6, $row[2], 'LR', 0, 'C'); 
            $this->Ln();
        }
        // Closing line
        $this->Cell(array_sum($w), 0, '', 'T');
        $this->Ln(10);
    }
}


/**
 * Fetches the historical academic data for a given student.
 *
 * @param int $student_id The ID of the student.
 * @return array An array of historical data, grouped by period.
 */
function get_student_history($student_id) {
    try {
        $pdo = getDBConnection();
        
        // 1. Get all past periods (closed or published)
        $stmt_periods = $pdo->prepare("
            SELECT id, nom
            FROM periodes
            WHERE statut IN ('fermee', 'publiee')
            ORDER BY date_debut_saisie DESC
        ");
        $stmt_periods->execute();
        $periods = $stmt_periods->fetchAll();

        $history = [];

        // 2. For each period, get the student's grades
        foreach ($periods as $period) {
            $stmt_grades = $pdo->prepare("
                SELECT 
                    m.nom as matiere_nom,
                    moy.moyenne
                FROM moyennes moy
                JOIN matieres m ON moy.matiere_id = m.id
                WHERE moy.etudiant_id = :student_id AND moy.periode_id = :period_id
                ORDER BY m.nom ASC
            ");
            $stmt_grades->execute([
                ':student_id' => $student_id,
                ':period_id' => $period['id']
            ]);
            $grades = $stmt_grades->fetchAll();

            if (!empty($grades)) {
                $history[] = [
                    'period' => $period['nom'],
                    'subjects' => array_map(function($grade) {
                        return [
                            'name' => $grade['matiere_nom'],
                            'grade' => $grade['moyenne']
                        ];
                    }, $grades)
                ];
            }
        }
        
        return $history;

    } catch (Exception $e) {
        // In a real app, you might log this error
        error_log("History fetch error: " . $e->getMessage());
        return []; // Return empty on error
    }
}

/**
 * Fetches all published periods a student is enrolled in.
 *
 * @param int $student_id The ID of the student.
 * @return array A list of published periods.
 */
function get_published_periods_for_student($student_id) {
    try {
        $pdo = getDBConnection();
        
        $stmt = $pdo->prepare("
            SELECT DISTINCT p.id, p.nom
            FROM periodes p
            JOIN inscriptions_matieres im ON p.id = im.periode_id
            WHERE im.etudiant_id = :student_id AND p.statut = 'publiee'
            ORDER BY p.date_debut_saisie DESC
        ");
        $stmt->execute([':student_id' => $student_id]);
        
        return $stmt->fetchAll();

    } catch (Exception $e) {
        error_log("Failed to fetch published periods: " . $e->getMessage());
        return [];
    }
}

/**
 * Gère la génération du relevé de notes en PDF.
 */
function handle_generate_transcript() {
    
    if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'etudiant') {
        // This should not be reachable due to router authorization
        header('Location: ' . APP_URL);
        exit;
    }

    $etudiant_id = $_SESSION['user_id'];
    $periode_id = filter_input(INPUT_GET, 'periode_id', FILTER_VALIDATE_INT);

    if (!$periode_id) {
        $_SESSION['error_message'] = "ID de periode non valide pour generer le releve.";
        header('Location: ' . APP_URL . '/index.php?page=dashboard_student');
        exit;
    }

    try {
        $pdo = getDBConnection();

        // 1. Get Student and Period info
        $stmt_info = $pdo->prepare("
            SELECT u.nom, u.prenom, p.nom as periode_nom, p.annee_universitaire
            FROM utilisateurs u, periodes p
            WHERE u.id = ? AND p.id = ? AND p.statut = 'publiee'
        ");
        $stmt_info->execute([$etudiant_id, $periode_id]);
        $info = $stmt_info->fetch();

        if (!$info) {
            throw new Exception("Impossible de trouver les informations pour le releve de notes ou la periode n'est pas publiee.");
        }

        // 2. Get all grades and subject info for the student and period
        $stmt_grades = $pdo->prepare("
            SELECT 
                m.nom as matiere_nom,
                m.coefficient,
                m.seuil_validation,
                moy.moyenne
            FROM inscriptions_matieres im
            JOIN matieres m ON im.matiere_id = m.id
            LEFT JOIN moyennes moy ON moy.etudiant_id = im.etudiant_id AND moy.matiere_id = m.id AND moy.periode_id = im.periode_id
            WHERE im.etudiant_id = ? AND im.periode_id = ?
            ORDER BY m.nom ASC
        ");
        $stmt_grades->execute([$etudiant_id, $periode_id]);
        $grades_data = $stmt_grades->fetchAll();

        // 3. Create PDF
        $student_name = $info['prenom'] . ' ' . $info['nom'];
        $pdf = new TranscriptPDF($student_name, $info['annee_universitaire']);
        $pdf->AliasNbPages();
        $pdf->AddPage();
        
        // Chapter for the period
        $pdf->ChapterTitle(mb_convert_encoding($info['periode_nom'], 'ISO-8859-1', 'UTF-8'));
        
        // Table header
        $header = array(
            mb_convert_encoding('Matière (Coeff)', 'ISO-8859-1', 'UTF-8'), 
            mb_convert_encoding('Note / 20', 'ISO-8859-1', 'UTF-8'), 
            mb_convert_encoding('Décision', 'ISO-8859-1', 'UTF-8')
        );

        $data_for_pdf = [];
        $total_points = 0;
        $total_coeffs = 0;

        foreach($grades_data as $grade) {
            $moyenne = $grade['moyenne'] ?? null;
            $seuil = $grade['seuil_validation'] ?? 10.0;
            $coeff = $grade['coefficient'] ?? 1;

            $decision_text = 'EN ATTENTE';
            if ($moyenne !== null) {
                if ($moyenne < 7) {
                    $decision_text = 'NON VALIDÉ';
                } elseif ($moyenne >= 7 && $moyenne < $seuil) {
                    $decision_text = 'RATTRAPAGE';
                } elseif ($moyenne >= $seuil) {
                    $decision_text = 'VALIDÉ';
                }

                if (is_numeric($coeff)) {
                    $total_points += $moyenne * $coeff;
                    $total_coeffs += $coeff;
                }
            }
            
            $data_for_pdf[] = [
                mb_convert_encoding($grade['matiere_nom'] . ' (' . $coeff . ')', 'ISO-8859-1', 'UTF-8'),
                mb_convert_encoding($moyenne !== null ? number_format($moyenne, 2, ',', ' ') : 'N/A', 'ISO-8859-1', 'UTF-8'),
                mb_convert_encoding($decision_text, 'ISO-8859-1', 'UTF-8')
            ];
        }

        if(empty($data_for_pdf)) {
            $pdf->SetFont('Helvetica', 'I', 10);
            $pdf->Cell(0, 10, mb_convert_encoding('Aucune note à afficher pour cette période.', 'ISO-8859-1', 'UTF-8'), 0, 1);
        } else {
             $pdf->SubjectTable($header, $data_for_pdf);
        }

        // Moyenne Générale
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

        $pdf->Ln(5);
        $pdf->SetFont('Helvetica', 'B', 12);
        $pdf->Cell(110, 10, mb_convert_encoding('Moyenne Générale:', 'ISO-8859-1', 'UTF-8'), 0, 0, 'R');
        $pdf->SetFont('Helvetica', 'B', 12);
        $pdf->Cell(30, 10, mb_convert_encoding($moyenne_generale !== null ? number_format($moyenne_generale, 2, ',', ' ') : 'N/A', 'ISO-8859-1', 'UTF-8'), 0, 0, 'C');
        $pdf->Cell(50, 10, mb_convert_encoding('(' . $decision_generale_text . ')', 'ISO-8859-1', 'UTF-8'), 0, 1, 'L');

        $pdf->Output('D', 'Releve_Notes_' . str_replace(' ', '_', $info['periode_nom']) . '.pdf');

    } catch (Exception $e) {
        // In case of error, redirect with a message
        $_SESSION['error_message'] = "Erreur lors de la generation du PDF: " . $e->getMessage();
        header('Location: ' . APP_URL . '/index.php?page=dashboard_student');
        exit;
    }
}
