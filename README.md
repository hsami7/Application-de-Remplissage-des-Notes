
Projet 5
📊 Application de Remplissage des Notes
Système dynamique de configuration des colonnes de notes par l'admin et saisie par les professeurs

Contexte et Problématique
La gestion des notes dans un établissement universitaire implique des structures variées selon les matières :

Certaines matières ont un DS + Examen, d'autres ont plusieurs TP + Projet + Examen
Les coefficients varient d'une matière à l'autre
Les formules de calcul de moyenne peuvent être différentes
Les périodes de saisie doivent être contrôlées (ouverture/fermeture)
Objectif : Développer une application où l'administrateur définit dynamiquement la structure des notes (colonnes, coefficients, formules) pour chaque matière, et où les professeurs saisissent les notes selon cette configuration.

🎯 Point Clé du Projet : Le système doit être entièrement dynamique. L'administrateur peut créer n'importe quelle structure de notes sans modification du code. Les professeurs voient automatiquement les colonnes définies pour leurs matières.
Exemple Concret de Configuration Dynamique
Matière	Colonnes Configurées	Formule de Moyenne
Mathématiques	DS1 (coef 1) + DS2 (coef 1) + Examen (coef 2)	(DS1 + DS2 + Examen × 2) / 4
Programmation Web	TP1 + TP2 + TP3 + Projet + Examen	((TP1+TP2+TP3)/3 × 0.3 + Projet × 0.3 + Examen × 0.4)
Anglais	Oral (coef 1) + Écrit (coef 1)	(Oral + Écrit) / 2
Projet Tutoré	Rapport + Soutenance + Code	MAX((R+S+C)/3, Soutenance)
Stage	Note Entreprise + Rapport + Soutenance	Entreprise × 0.4 + Rapport × 0.3 + Soutenance × 0.3
Acteurs du Système et Leurs Responsabilités
👨‍💼
Administrateur
L'administrateur configure entièrement la structure des notes pour chaque matière et gère les périodes de saisie.

📅 Gestion des Périodes
Création de période : L'administrateur crée une nouvelle période de notation (ex: "Semestre 1 - 2024-2025", "Session de rattrapage Juin 2025").
Dates d'ouverture/fermeture : Définition des dates pendant lesquelles les professeurs peuvent saisir les notes.
Statut de la période : À venir, Ouverte (saisie possible), Fermée (saisie bloquée), Publiée (notes visibles par les étudiants).
Prolongation : Possibilité d'étendre la date de fermeture si nécessaire.
Clôture anticipée : Fermeture manuelle de la période avant la date prévue.
⚙️ Configuration des Colonnes de Notes
Interface de configuration : Pour chaque matière et chaque période, l'administrateur définit les colonnes de notes via une interface graphique intuitive.
Ajout de colonne : Création d'une nouvelle colonne avec : nom (ex: "DS1", "TP2", "Projet"), note maximale (ex: 20, 100), coefficient, type (note normale, bonus, absence).
Ordre des colonnes : Réorganisation par glisser-déposer de l'ordre d'affichage des colonnes.
Modification : Modification des paramètres d'une colonne (uniquement si aucune note n'a été saisie).
Suppression : Suppression d'une colonne (avec confirmation, uniquement si vide).
Duplication : Copie de la configuration d'une autre matière ou d'une période précédente.
📐 Définition des Formules de Calcul
Éditeur de formule : Interface permettant de construire la formule de calcul de la moyenne.
Variables disponibles : Liste automatique des colonnes définies utilisables dans la formule.
Opérations supportées : Addition, soustraction, multiplication, division, MAX, MIN, MOYENNE, conditions.
Validation : Vérification de la syntaxe de la formule avant enregistrement.
Test : Possibilité de tester la formule avec des valeurs fictives.
Formules prédéfinies : Bibliothèque de formules courantes réutilisables.
👥 Gestion des Matières et Affectations
Création de matières : Ajout de nouvelles matières avec code, nom, filière, coefficient module.
Affectation professeurs : Association d'un ou plusieurs professeurs à une matière.
Groupes d'étudiants : Définition des groupes d'étudiants inscrits à chaque matière.
📊 Suivi et Validation
Tableau de bord de progression : Vue globale du pourcentage de saisie pour chaque matière (0%, 50%, 100%).
Relances : Envoi d'emails de rappel aux professeurs en retard.
Validation globale : Vérification et validation de toutes les notes avant publication.
Publication : Rendre les notes visibles aux étudiants.
Génération des documents : Création des PV de délibération et relevés de notes.
📄 Rapports et Exports
PV de délibération : Document officiel avec toutes les notes et moyennes.
Relevés de notes : Document individuel pour chaque étudiant.
Export Excel : Export des données brutes pour traitement externe.
Statistiques : Moyennes par matière, taux de réussite, distribution des notes.
👨‍🏫
Professeur
Le professeur saisit les notes de ses étudiants dans les colonnes définies par l'administrateur.

📋 Consultation des Matières
Mes matières : Liste des matières dont le professeur est responsable pour la période en cours.
Structure définie : Visualisation des colonnes de notes configurées par l'admin (lecture seule).
Période active : Indication claire si la période est ouverte à la saisie ou fermée.
Deadline : Affichage de la date limite de saisie.
✏️ Saisie des Notes
Interface type tableur : Tableau avec étudiants en lignes et colonnes de notes définies dynamiquement.
Navigation clavier : Tab pour passer à la cellule suivante, Entrée pour valider, flèches pour se déplacer.
Validation en temps réel : Vérification immédiate que la note est dans la plage autorisée (0 à note_max).
Statuts spéciaux : Possibilité de saisir ABS (absent), DIS (dispensé), DEF (défaillant) au lieu d'une note.
Sauvegarde automatique : Enregistrement automatique après chaque modification.
Indicateur de progression : Barre montrant le pourcentage de notes saisies.
📤 Import de Notes
Template Excel : Téléchargement d'un fichier Excel pré-formaté avec les colonnes correctes.
Import : Upload du fichier rempli, vérification et import des notes.
Rapport d'import : Liste des notes importées avec succès et des erreurs éventuelles.
📊 Visualisation des Résultats
Moyennes calculées : Affichage automatique de la moyenne calculée selon la formule définie.
Statistiques de la matière : Moyenne générale, note min, note max, écart-type, distribution.
Graphique de distribution : Histogramme des notes.
✅ Validation de la Saisie
Vérification complète : Avant validation, le système vérifie qu'aucune note n'est manquante.
Bouton de validation : Confirmation que la saisie est terminée et correcte.
Verrouillage : Après validation, les notes ne peuvent plus être modifiées (sauf par l'admin).
Notification à l'admin : L'administrateur est informé que la saisie est complète.
👨‍🎓
Étudiant
L'étudiant consulte ses notes une fois celles-ci publiées par l'administration.

📊 Consultation des Notes
Tableau des notes : Vue de toutes les notes de la période avec le détail par matière.
Colonnes dynamiques : Affichage des colonnes telles que configurées par l'admin (DS1, TP, Examen, etc.).
Moyennes : Moyenne par matière calculée automatiquement, moyenne générale.
Statut : Indication si la matière est validée ou non (selon le seuil défini).
📄 Documents
Relevé de notes : Téléchargement du relevé de notes officiel au format PDF.
Attestation de réussite : Si applicable, génération d'une attestation.
Historique : Accès aux relevés des semestres/années précédents.
📈 Statistiques
Position : Rang de l'étudiant dans la promotion (si autorisé par l'admin).
Comparaison : Comparaison avec la moyenne de la promotion.
Évolution : Graphique d'évolution des moyennes au fil des semestres.
Workflow de Gestion des Notes
Étape 1
⚙️ Config. Structure
→
Étape 2
📐 Définition Formules
→
Étape 3
🔓 Ouverture Période
→
Étape 4
✏️ Saisie Profs
→
Étape 5
✅ Validation Prof
→
Étape 6
🔒 Fermeture
→
Étape 7
📢 Publication
Fonctionnalités Techniques
⚙️ Configurateur de Colonnes
Interface graphique de configuration dynamique.

Ajout/suppression de colonnes
Réorganisation par drag & drop
Types : note, bonus, malus, absence
Configuration note max et coefficient
Duplication de configurations
Templates prédéfinis
📐 Éditeur de Formules
Création de formules de calcul personnalisées.

Interface visuelle ou textuelle
Variables automatiques (colonnes)
Fonctions : MAX, MIN, MOYENNE, SI
Validation de syntaxe
Test avec données fictives
Bibliothèque de formules
✏️ Interface de Saisie
Tableau de saisie type tableur.

Génération dynamique des colonnes
Navigation clavier fluide
Validation en temps réel
Sauvegarde automatique
Statuts spéciaux (ABS, DIS)
Import/Export Excel
🔢 Moteur de Calcul
Calcul sécurisé des moyennes.

Parser de formules sécurisé
Gestion des valeurs nulles
Calcul temps réel
Historisation des calculs
Recalcul sur modification
Vérification d'intégrité
📊 Module Statistiques
Analyses et visualisations.

Moyennes par matière/classe
Taux de réussite
Distribution des notes
Graphiques interactifs
Comparaisons historiques
Export des données
📄 Génération Documents
Documents officiels PDF.

Relevés de notes personnalisés
PV de délibération
Attestations
Templates configurables
Génération en masse
Filigrane et sécurité
Moteur de Calcul Sécurisé
⚠️ Attention Sécurité : Ne JAMAIS utiliser eval() pour évaluer les formules. Utiliser un parser mathématique dédié pour éviter les injections de code.
// Classe PHP pour l'évaluation sécurisée des formules

class FormulaParser {
    
    private $variables = [];
    private $operateurs = ['+', '-', '*', '/', '(', ')'];
    private $fonctions = ['MAX', 'MIN', 'MOYENNE', 'SI', 'ABS'];
    
    /**
     * Évalue une formule de manière sécurisée
     * @param string $formule Ex: "(DS1 + DS2 + Examen*2) / 4"
     * @param array $valeurs Ex: ['DS1' => 14, 'DS2' => 12, 'Examen' => 16]
     * @return float|null
     */
    public function evaluer($formule, $valeurs) {
        // 1. Validation de la formule
        if (!$this->validerFormule($formule)) {
            throw new InvalidFormulaException("Formule invalide");
        }
        
        // 2. Remplacer les variables par leurs valeurs
        $expression = $this->substituerVariables($formule, $valeurs);
        
        // 3. Tokenizer l'expression
        $tokens = $this->tokenize($expression);
        
        // 4. Convertir en notation polonaise inverse (RPN)
        $rpn = $this->versRPN($tokens);
        
        // 5. Évaluer la RPN
        return $this->evaluerRPN($rpn);
    }
    
    /**
     * Valide qu'une formule ne contient que des éléments autorisés
     */
    private function validerFormule($formule) {
        // Whitelist stricte des caractères autorisés
        $pattern = '/^[A-Za-z0-9_+\-*\/().,%\s]+$/';
        
        if (!preg_match($pattern, $formule)) {
            return false;
        }
        
        // Vérifier les parenthèses équilibrées
        $compteur = 0;
        foreach (str_split($formule) as $char) {
            if ($char === '(') $compteur++;
            if ($char === ')') $compteur--;
            if ($compteur < 0) return false;
        }
        
        return $compteur === 0;
    }
    
    /**
     * Remplace les noms de variables par leurs valeurs numériques
     */
    private function substituerVariables($formule, $valeurs) {
        foreach ($valeurs as $nom => $valeur) {
            // Gérer les absences
            if ($valeur === 'ABS' || $valeur === null) {
                $valeur = 'NULL';
            }
            // Remplacement avec frontières de mot
            $formule = preg_replace('/\b' . preg_quote($nom) . '\b/', $valeur, $formule);
        }
        return $formule;
    }
    
    /**
     * Évalue la fonction MOYENNE avec gestion des NULL
     */
    private function fonctionMoyenne($valeurs) {
        $valeursValides = array_filter($valeurs, fn($v) => $v !== null);
        
        if (count($valeursValides) === 0) {
            return null;
        }
        
        return array_sum($valeursValides) / count($valeursValides);
    }
    
    /**
     * Évalue la fonction SI (condition ternaire)
     * SI(condition, valeur_si_vrai, valeur_si_faux)
     */
    private function fonctionSi($condition, $siVrai, $siFaux) {
        return $condition ? $siVrai : $siFaux;
    }
}

// Exemple d'utilisation
$parser = new FormulaParser();

$formule = "(DS1 + DS2 + Examen * 2) / 4";
$notes = [
    'DS1' => 14,
    'DS2' => 12,
    'Examen' => 16
];

$moyenne = $parser->evaluer($formule, $notes);
// Résultat : (14 + 12 + 16*2) / 4 = (14 + 12 + 32) / 4 = 14. 5
                        
Schéma de Base de Données
-- =============================================
-- TABLE DES PÉRIODES DE NOTATION
-- =============================================
CREATE TABLE periodes (
    id                  INT PRIMARY KEY AUTO_INCREMENT,
    nom                 VARCHAR(100) NOT NULL,
    code                VARCHAR(20) UNIQUE NOT NULL,  -- "S1-2024", "RAT-2025"
    annee_universitaire VARCHAR(9) NOT NULL,  -- "2024-2025"
    type                ENUM('semestre', 'trimestre', 'session', 'rattrapage') NOT NULL,
    date_debut_saisie   DATETIME NOT NULL,
    date_fin_saisie     DATETIME NOT NULL,
    statut              ENUM('a_venir', 'ouverte', 'fermee', 'publiee') DEFAULT 'a_venir',
    date_publication    DATETIME,
    date_creation       TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- =============================================
-- TABLE DES FILIÈRES
-- =============================================
CREATE TABLE filieres (
    id                  INT PRIMARY KEY AUTO_INCREMENT,
    code                VARCHAR(20) UNIQUE NOT NULL,
    nom                 VARCHAR(150) NOT NULL,
    niveau              VARCHAR(20),  -- "Licence", "Master"
    responsable_id      INT,
    date_creation       TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- =============================================
-- TABLE DES MATIÈRES
-- =============================================
CREATE TABLE matieres (
    id                  INT PRIMARY KEY AUTO_INCREMENT,
    code                VARCHAR(20) UNIQUE NOT NULL,
    nom                 VARCHAR(150) NOT NULL,
    filiere_id          INT NOT NULL,
    coefficient         DECIMAL(3,1) DEFAULT 1,
    credits             INT,
    seuil_validation    DECIMAL(4,2) DEFAULT 10,
    date_creation       TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (filiere_id) REFERENCES filieres(id)
);

-- =============================================
-- TABLE DES AFFECTATIONS PROFESSEURS-MATIÈRES
-- =============================================
CREATE TABLE affectations_profs (
    id                  INT PRIMARY KEY AUTO_INCREMENT,
    professeur_id       INT NOT NULL,
    matiere_id          INT NOT NULL,
    periode_id          INT NOT NULL,
    groupe              VARCHAR(50),  -- "Groupe A", "Tous"
    FOREIGN KEY (professeur_id) REFERENCES utilisateurs(id),
    FOREIGN KEY (matiere_id) REFERENCES matieres(id),
    FOREIGN KEY (periode_id) REFERENCES periodes(id),
    UNIQUE KEY unique_affectation (professeur_id, matiere_id, periode_id, groupe)
);

-- =============================================
-- TABLE DE CONFIGURATION DES COLONNES (DYNAMIQUE)
-- C'est ici que la magie opère !
-- =============================================
CREATE TABLE configuration_colonnes (
    id                  INT PRIMARY KEY AUTO_INCREMENT,
    matiere_id          INT NOT NULL,
    periode_id          INT NOT NULL,
    nom_colonne         VARCHAR(50) NOT NULL,  -- "DS1", "TP2", "Examen"
    code_colonne        VARCHAR(20) NOT NULL,  -- "DS1", "TP2" (pour les formules)
    type                ENUM('note', 'bonus', 'malus', 'info') DEFAULT 'note',
    note_max            DECIMAL(5,2) DEFAULT 20,
    coefficient         DECIMAL(3,1) DEFAULT 1,
    obligatoire         BOOLEAN DEFAULT TRUE,
    ordre               INT NOT NULL,
    date_creation       TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (matiere_id) REFERENCES matieres(id),
    FOREIGN KEY (periode_id) REFERENCES periodes(id),
    UNIQUE KEY unique_colonne (matiere_id, periode_id, code_colonne)
);-- =============================================
-- TABLE DES FORMULES DE CALCUL
-- =============================================
CREATE TABLE formules (
    id                  INT PRIMARY KEY AUTO_INCREMENT,
    matiere_id          INT NOT NULL,
    periode_id          INT NOT NULL,
    formule             TEXT NOT NULL,  -- "(DS1 + DS2 + Examen*2) / 4"
    description         VARCHAR(255),
    date_creation       TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    date_modification   TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (matiere_id) REFERENCES matieres(id),
    FOREIGN KEY (periode_id) REFERENCES periodes(id),
    UNIQUE KEY unique_formule (matiere_id, periode_id)
);

-- =============================================
-- TABLE DES INSCRIPTIONS ÉTUDIANTS AUX MATIÈRES
-- =============================================
CREATE TABLE inscriptions_matieres (
    id                  INT PRIMARY KEY AUTO_INCREMENT,
    etudiant_id         INT NOT NULL,
    matiere_id          INT NOT NULL,
    periode_id          INT NOT NULL,
    groupe              VARCHAR(50),
    dispense            BOOLEAN DEFAULT FALSE,
    date_inscription    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (etudiant_id) REFERENCES utilisateurs(id),
    FOREIGN KEY (matiere_id) REFERENCES matieres(id),
    FOREIGN KEY (periode_id) REFERENCES periodes(id),
    UNIQUE KEY unique_inscription (etudiant_id, matiere_id, periode_id)
);

-- =============================================
-- TABLE DES NOTES (DONNÉES SAISIES)
-- Stockage flexible :  une ligne par note saisie
-- =============================================
CREATE TABLE notes (
    id                  INT PRIMARY KEY AUTO_INCREMENT,
    etudiant_id         INT NOT NULL,
    colonne_id          INT NOT NULL,  -- Référence à configuration_colonnes
    valeur              DECIMAL(5,2),  -- NULL si absence/non saisi
    statut              ENUM('saisie', 'absent', 'dispense', 'defaillant') DEFAULT 'saisie',
    saisi_par           INT NOT NULL,
    date_saisie         TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    date_modification   TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (etudiant_id) REFERENCES utilisateurs(id),
    FOREIGN KEY (colonne_id) REFERENCES configuration_colonnes(id),
    FOREIGN KEY (saisi_par) REFERENCES utilisateurs(id),
    UNIQUE KEY unique_note (etudiant_id, colonne_id)
);

-- =============================================
-- TABLE DES MOYENNES CALCULÉES
-- Résultats pré-calculés pour performance
-- =============================================
CREATE TABLE moyennes (
    id                  INT PRIMARY KEY AUTO_INCREMENT,
    etudiant_id         INT NOT NULL,
    matiere_id          INT NOT NULL,
    periode_id          INT NOT NULL,
    moyenne             DECIMAL(5,2),
    rang                INT,
    decision            ENUM('valide', 'non_valide', 'rattrapage', 'en_attente') DEFAULT 'en_attente',
    credits_obtenus     INT,
    date_calcul         TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (etudiant_id) REFERENCES utilisateurs(id),
    FOREIGN KEY (matiere_id) REFERENCES matieres(id),
    FOREIGN KEY (periode_id) REFERENCES periodes(id),
    UNIQUE KEY unique_moyenne (etudiant_id, matiere_id, periode_id)
);

-- =============================================
-- TABLE DE PROGRESSION DE SAISIE
-- Suivi de l'avancement par matière
-- =============================================
CREATE TABLE progression_saisie (
    id                  INT PRIMARY KEY AUTO_INCREMENT,
    matiere_id          INT NOT NULL,
    periode_id          INT NOT NULL,
    professeur_id       INT NOT NULL,
    total_etudiants     INT NOT NULL,
    total_notes_attendues INT NOT NULL,  -- nb_etudiants × nb_colonnes
    notes_saisies       INT DEFAULT 0,
    pourcentage         DECIMAL(5,2) DEFAULT 0,
    valide_par_prof     BOOLEAN DEFAULT FALSE,
    date_validation     DATETIME,
    date_mise_a_jour    TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (matiere_id) REFERENCES matieres(id),
    FOREIGN KEY (periode_id) REFERENCES periodes(id),
    FOREIGN KEY (professeur_id) REFERENCES utilisateurs(id),
    UNIQUE KEY unique_progression (matiere_id, periode_id)
);

-- =============================================
-- TABLE D'HISTORIQUE DES MODIFICATIONS
-- Audit trail complet
-- =============================================
CREATE TABLE historique_notes (
    id                  INT PRIMARY KEY AUTO_INCREMENT,
    note_id             INT NOT NULL,
    ancienne_valeur     DECIMAL(5,2),
    nouvelle_valeur     DECIMAL(5,2),
    ancien_statut       VARCHAR(20),
    nouveau_statut      VARCHAR(20),
    modifie_par         INT NOT NULL,
    motif               TEXT,
    adresse_ip          VARCHAR(45),
    date_modification   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (note_id) REFERENCES notes(id),
    FOREIGN KEY (modifie_par) REFERENCES utilisateurs(id)
);

-- =============================================
-- TABLE DES TEMPLATES DE FORMULES
-- Bibliothèque de formules réutilisables
-- =============================================
CREATE TABLE templates_formules (
    id                  INT PRIMARY KEY AUTO_INCREMENT,
    nom                 VARCHAR(100) NOT NULL,
    description         TEXT,
    colonnes_requises   JSON NOT NULL,  -- ["DS1", "DS2", "Examen"]
    formule             TEXT NOT NULL,
    categorie           VARCHAR(50),  -- "Standard", "Avec bonus", etc.
    date_creation       TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Données initiales :  templates de formules courantes
INSERT INTO templates_formules (nom, description, colonnes_requises, formule, categorie) VALUES
('Moyenne simple', 'Moyenne arithmétique de toutes les notes', 
 '["Note1", "Note2"]', 'MOYENNE(Note1, Note2)', 'Standard'),
('DS + Examen', 'DS coefficient 1, Examen coefficient 2', 
 '["DS", "Examen"]', '(DS + Examen * 2) / 3', 'Standard'),
('Meilleure des deux', 'Garde la meilleure note entre deux évaluations', 
 '["Note1", "Note2"]', 'MAX(Note1, Note2)', 'Spécial'),
('TP + Projet + Examen', 'Moyenne TP 30%, Projet 30%, Examen 40%', 
 '["TP", "Projet", "Examen"]', 'TP * 0.3 + Projet * 0.3 + Examen * 0.4', 'Standard');

-- =============================================
-- INDEX POUR PERFORMANCE
-- =============================================
CREATE INDEX idx_notes_etudiant ON notes(etudiant_id);
CREATE INDEX idx_notes_colonne ON notes(colonne_id);
CREATE INDEX idx_config_matiere_periode ON configuration_colonnes(matiere_id, periode_id);
CREATE INDEX idx_moyennes_periode ON moyennes(periode_id);
                        
Exigences de Sécurité
🔢 Intégrité des Notes
Historisation complète de toutes les modifications avec ancien/nouvelle valeur, utilisateur, date, IP. Impossible de supprimer l'historique.

🔒 Verrouillage des Périodes
Une fois la période fermée, aucune modification possible sauf par l'administrateur avec justification obligatoire.

📐 Évaluation Sécurisée des Formules
Utilisation d'un parser mathématique dédié. JAMAIS d'eval(). Validation stricte des formules avant enregistrement.

🔐 Contrôle d'Accès
Un professeur ne peut saisir que les notes des matières qui lui sont affectées. Vérification systématique à chaque requête.

✍️ Signature des Documents
PV et relevés générés avec hash de vérification. Filigrane sur les documents PDF. Horodatage certifié.

💾 Sauvegarde
Backup automatique avant chaque période de délibération. Conservation des archives pendant 10 ans minimum.

Livrables Attendus
⚙️
Configurateur Dynamique
Interface admin pour définir colonnes et formules.

✏️
Interface de Saisie
Tableau type Excel avec génération dynamique.

🔢
Moteur de Calcul
Parser sécurisé pour évaluation des formules.

📄
Générateur PDF
PV de délibération et relevés de notes.

Projet 5 - Application de Remplissage des Notes

Technologies : HTML5 | CSS3 | PHP 8+ | MySQL