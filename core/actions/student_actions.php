<?php
require_once __DIR__ . '/../lib/fpdf/fpdf.php';

/**
 * Custom PDF class to create header and footer for the transcript.
 */
class TranscriptPDF extends FPDF
{
    private $studentName;
    private $schoolYear;
    private $filiereNom;
    private $filiereNiveau;
    private $periodeNom;

    function __construct($studentName = '', $schoolYear = '', $filiereNom = '', $filiereNiveau = '', $periodeNom = '', $orientation = 'P', $unit = 'mm', $size = 'A4') {
        parent::__construct($orientation, $unit, $size);
        $this->studentName = $studentName;
        $this->schoolYear = $schoolYear;
        $this->filiereNom = $filiereNom;
        $this->filiereNiveau = $filiereNiveau;
        $this->periodeNom = $periodeNom;
    }

    // Page header
    function Header()
    {
        // Logo (Left), University Name (Center), Academic Year (Right)
        $this->Image(ROOT_PATH . '/public/img/logo.png', 10, 8, 25); // (x, y, width)
        
        // Move to the right of the logo to start printing university name
        $this->SetX(40); 
        $this->SetFont('Helvetica', 'B', 16);
        $this->Cell(120, 10, mb_convert_encoding('Université Euromed de Fes', 'ISO-8859-1', 'UTF-8'), 0, 0, 'C'); // Center aligned within its width
        
        // Academic Year (Right)
        $this->SetX(160); // Position for academic year
        $this->SetFont('Helvetica', '', 10);
        $this->Cell(40, 10, mb_convert_encoding($this->schoolYear, 'ISO-8859-1', 'UTF-8'), 0, 1, 'R'); // Right aligned

        // Line 2: Department (Center)
        $this->SetFont('Helvetica', '', 10);
        $this->Cell(0, 6, mb_convert_encoding('Département Informatique', 'ISO-8859-1', 'UTF-8'), 0, 1, 'C');
        $this->Ln(10); 

        // First horizontal separator line
        $this->Line(10, $this->GetY(), 200, $this->GetY());
        $this->Ln(5); 

        // Document Title
        $this->SetFont('Helvetica', 'B', 18);
        $this->Cell(0, 10, mb_convert_encoding('RELEVÉ DE NOTES OFFICIEL', 'ISO-8859-1', 'UTF-8'), 0, 1, 'C');
        $this->Ln(5); 

        // Second horizontal separator line
        $this->Line(10, $this->GetY(), 200, $this->GetY());
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
    
    // Student details section
    function StudentDetails()
    {
        $this->SetFont('Helvetica', 'B', 10);
        $this->Cell(95, 7, mb_convert_encoding('ETUDIANT:', 'ISO-8859-1', 'UTF-8'), 0, 0, 'L');
        $this->Cell(95, 7, mb_convert_encoding('SESSION:', 'ISO-8859-1', 'UTF-8'), 0, 1, 'L');

        $this->SetFont('Helvetica', '', 10);
        $this->Cell(95, 7, mb_convert_encoding('Nom: ' . $this->studentName, 'ISO-8859-1', 'UTF-8'), 0, 0, 'L');
        $this->Cell(95, 7, mb_convert_encoding($this->periodeNom, 'ISO-8859-1', 'UTF-8'), 0, 1, 'L');

        $this->Cell(95, 7, '', 0, 0, 'L'); // Empty cell for alignment
        $this->Cell(95, 7, mb_convert_encoding('Filière: ' . $this->filiereNom, 'ISO-8859-1', 'UTF-8'), 0, 1, 'L');
        $this->Ln(5);

        // Horizontal Separator
        $this->Line(10, $this->GetY(), 200, $this->GetY());
        $this->Ln(10);
    }
    
    // Subject table
    function SubjectTable($header, $data)
    {
        // Column widths for 4 columns (Module/Matiere, Coeff, Note, Résultat)
        $w = array(90, 20, 40, 40); 
        // Header
        $this->SetFont('Helvetica', 'B', 10);
        for($i=0; $i<count($header); $i++)
            $this->Cell($w[$i], 7, mb_convert_encoding($header[$i], 'ISO-8859-1', 'UTF-8'), 1, 0, 'C');
        $this->Ln();
        // Data
        $this->SetFont('Helvetica', '', 10);
        foreach($data as $row)
        {
            $this->Cell($w[0], 6, mb_convert_encoding($row[0], 'ISO-8859-1', 'UTF-8'), 'LR'); // Module/Matiere
            $this->Cell($w[1], 6, mb_convert_encoding($row[1], 'ISO-8859-1', 'UTF-8'), 'LR', 0, 'C'); // Coeff
            $this->Cell($w[2], 6, mb_convert_encoding($row[2], 'ISO-8859-1', 'UTF-8'), 'LR', 0, 'C'); // Note
            $this->Cell($w[3], 6, mb_convert_encoding($row[3], 'ISO-8859-1', 'UTF-8'), 'LR', 0, 'C'); // Résultat
            $this->Ln();
        }
        // Closing line
        $this->Cell(array_sum($w), 0, '', 'T');
        $this->Ln(10);
    }
    
    // Signatory section
    function SignatorySection()
    {
        $this->Ln(20); // Some space after the summary

        // "Fait à Fès, le DD/MM/YYYY"
        $this->SetFont('Helvetica', '', 10);
        $this->Cell(0, 10, mb_convert_encoding('Fait à Fès, le ' . date('d/m/Y'), 'ISO-8859-1', 'UTF-8'), 0, 1, 'L');
        $this->Ln(15);

        // "Le Directeur Pédagogique"
        $this->SetFont('Helvetica', 'B', 10);
        $this->Cell(0, 5, mb_convert_encoding('Le Directeur Pédagogique', 'ISO-8859-1', 'UTF-8'), 0, 1, 'L');
        
        // "(Signature & Cachet)"
        $this->SetFont('Helvetica', '', 8);
        $this->Cell(0, 5, mb_convert_encoding('(Signature & Cachet)', 'ISO-8859-1', 'UTF-8'), 0, 1, 'L');
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

    // Temporarily disable error reporting for this function to prevent PDF generation issues.

    error_reporting(0);

    ini_set('display_errors', 0);

    

    if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'etudiant') {

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



        // 1. Get Student, Period, and Filiere info

        $stmt_info = $pdo->prepare("

            SELECT 

                u.nom, u.prenom, 

                p.nom as periode_nom, p.annee_universitaire,

                GROUP_CONCAT(DISTINCT f.nom SEPARATOR ', ') as filiere_nom,

                GROUP_CONCAT(DISTINCT f.niveau SEPARATOR ', ') as filiere_niveau

            FROM utilisateurs u

            JOIN inscriptions_matieres im ON u.id = im.etudiant_id

            JOIN matieres m ON im.matiere_id = m.id

            JOIN filieres f ON m.filiere_id = f.id

            JOIN periodes p ON im.periode_id = p.id

            WHERE u.id = :etudiant_id AND p.id = :periode_id AND p.statut = 'publiee'

            GROUP BY u.id, p.id, u.nom, u.prenom, p.nom, p.annee_universitaire

        ");

        $stmt_info->execute([

            ':etudiant_id' => $etudiant_id,

            ':periode_id' => $periode_id

        ]);

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

                moy.moyenne,

                moy.decision

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

        $pdf = new TranscriptPDF($student_name, $info['annee_universitaire'], $info['filiere_nom'], $info['filiere_niveau'], $info['periode_nom']);

        $pdf->AliasNbPages();

        $pdf->AddPage();

        

        $pdf->StudentDetails();

        

        // Table header

        $header = array(

            mb_convert_encoding('MODULE / MATIERE', 'ISO-8859-1', 'UTF-8'), 

            mb_convert_encoding('COEFF', 'ISO-8859-1', 'UTF-8'), 

            mb_convert_encoding('NOTE', 'ISO-8859-1', 'UTF-8'), 

            mb_convert_encoding('RESULTAT', 'ISO-8859-1', 'UTF-8')

        );



        $data_for_pdf = [];

        $total_points = 0;

        $total_coeffs = 0;

        

        // --- New Logic ---

        if (!defined('SEUIL_VALIDATION')) define('SEUIL_VALIDATION', 10);

        if (!defined('NOTE_ELIMINATOIRE')) define('NOTE_ELIMINATOIRE', 5);

        $notes_eliminatoires = false;

        // --- End New Logic ---



        foreach($grades_data as $grade) {

            $moyenne = $grade['moyenne'] ?? null;

            $coeff = $grade['coefficient'] ?? 1;



            if ($moyenne !== null && $moyenne < NOTE_ELIMINATOIRE) {

                $notes_eliminatoires = true;

            }

            

            $decision_text = $grade['decision'] ? strtoupper($grade['decision']) : 'EN ATTENTE';



            if (is_numeric($moyenne) && is_numeric($coeff)) {

                $total_points += $moyenne * $coeff;

                $total_coeffs += $coeff;

            }

            

            $data_for_pdf[] = [

                mb_convert_encoding($grade['matiere_nom'], 'ISO-8859-1', 'UTF-8'), // Matière

                $coeff, // Coeff (as number, FPDF will convert to string)

                mb_convert_encoding($moyenne !== null ? number_format($moyenne, 2, ',', ' ') : 'N/A', 'ISO-8859-1', 'UTF-8'), // Note

                mb_convert_encoding($decision_text, 'ISO-8859-1', 'UTF-8') // Résultat

            ];

        }



        if(empty($data_for_pdf)) {

            $pdf->SetFont('Helvetica', 'I', 10);

            $pdf->Cell(0, 10, mb_convert_encoding('Aucune note à afficher pour cette période.', 'ISO-8859-1', 'UTF-8'), 0, 1);

        } else {

             $pdf->SubjectTable($header, $data_for_pdf);

        }



        // --- New Summary and Decision Logic ---

        $moyenne_generale = ($total_coeffs > 0) ? $total_points / $total_coeffs : null;



        $decision_finale = 'EN ATTENTE';

        if ($moyenne_generale !== null) {

            if ($moyenne_generale >= SEUIL_VALIDATION && !$notes_eliminatoires) {

                $decision_finale = "ADMIS";

            } elseif ($moyenne_generale >= SEUIL_VALIDATION && $notes_eliminatoires) {

                $decision_finale = "RATTRAPAGE"; // Note Éliminatoire

            } else { // $moyenne_generale < SEUIL_VALIDATION

                $decision_finale = "RATTRAPAGE";

            }

        }

        // --- End New Summary and Decision Logic ---

        

        // --- Mention (Honors) Logic ---

        $mention_text = '';

        if ($decision_finale === 'ADMIS') {

            if ($moyenne_generale >= 16) {

                $mention_text = 'Très Bien';

            } elseif ($moyenne_generale >= 14) {

                $mention_text = 'Bien';

            } elseif ($moyenne_generale >= 12) {

                $mention_text = 'Assez Bien';

            } else {

                $mention_text = 'Passable';

            }

        }

        // --- End Mention Logic ---

        

        // PDF Summary Section

        $pdf->Ln(10);

        $pdf->Line(10, $pdf->GetY(), 200, $pdf->GetY()); // Horizontal Separator

        $pdf->Ln(5);



        // TOTAL COEFF

        $pdf->SetFont('Helvetica', '', 10);

        $pdf->Cell(140, 7, mb_convert_encoding('TOTAL COEFF:', 'ISO-8859-1', 'UTF-8'), 0, 0, 'R');

        $pdf->SetFont('Helvetica', 'B', 10);

        $pdf->Cell(50, 7, $total_coeffs, 0, 1, 'L');



        // MOYENNE GÉNÉRALE

        $moyenne_generale_formatted = $moyenne_generale !== null ? number_format($moyenne_generale, 2, ',', ' ') : 'N/A';

        $pdf->SetFont('Helvetica', '', 10);

        $pdf->Cell(140, 7, mb_convert_encoding('MOYENNE GÉNÉRALE:', 'ISO-8859-1', 'UTF-8'), 0, 0, 'R');

        $pdf->SetFont('Helvetica', 'B', 10);

        $pdf->Cell(50, 7, mb_convert_encoding($moyenne_generale_formatted . ' / 20', 'ISO-8859-1', 'UTF-8'), 0, 1, 'L');

        

        // DECISION

        $decision_display = $decision_finale;

        if (!empty($mention_text)) {

            $decision_display .= ' (Mention ' . $mention_text . ')';

        }

        $pdf->SetFont('Helvetica', '', 10);

        $pdf->Cell(140, 7, mb_convert_encoding('DECISION:', 'ISO-8859-1', 'UTF-8'), 0, 0, 'R');

        $pdf->SetFont('Helvetica', 'B', 10);

        $pdf->Cell(50, 7, mb_convert_encoding($decision_display, 'ISO-8859-1', 'UTF-8'), 0, 1, 'L');

        

        $pdf->Line(10, $pdf->GetY(), 200, $pdf->GetY()); // Horizontal Separator

        $pdf->Ln(10);



        $pdf->SignatorySection(); // Call the new signatory section



        $pdf->Output('D', 'Releve_de_note_' . str_replace(' ', '_', $student_name) . '_' . str_replace(' ', '_', $info['periode_nom']) . '.pdf');



    } catch (Exception $e) {

        // In case of error, redirect with a message

        $_SESSION['error_message'] = "Erreur lors de la generation du PDF: " . $e->getMessage();

        header('Location: ' . APP_URL . '/index.php?page=dashboard_student');

        exit;

    }

}
