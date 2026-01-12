-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 12, 2026 at 07:28 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `notes_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `affectations_profs`
--

CREATE TABLE `affectations_profs` (
  `id` int(11) NOT NULL,
  `professeur_id` int(11) NOT NULL,
  `matiere_id` int(11) NOT NULL,
  `periode_id` int(11) NOT NULL,
  `groupe` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `affectations_profs`
--

INSERT INTO `affectations_profs` (`id`, `professeur_id`, `matiere_id`, `periode_id`, `groupe`) VALUES
(2, 3, 3, 1, NULL),
(4, 3, 4, 1, NULL),
(1, 3, 5, 1, NULL),
(3, 4, 9, 1, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `configuration_colonnes`
--

CREATE TABLE `configuration_colonnes` (
  `id` int(11) NOT NULL,
  `matiere_id` int(11) NOT NULL,
  `periode_id` int(11) NOT NULL,
  `nom_colonne` varchar(50) NOT NULL,
  `code_colonne` varchar(20) NOT NULL,
  `type` enum('note','bonus','malus','info') DEFAULT 'note',
  `note_max` decimal(5,2) DEFAULT 20.00,
  `coefficient` decimal(3,1) DEFAULT 1.0,
  `obligatoire` tinyint(1) DEFAULT 1,
  `ordre` int(11) NOT NULL,
  `date_creation` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `configuration_colonnes`
--

INSERT INTO `configuration_colonnes` (`id`, `matiere_id`, `periode_id`, `nom_colonne`, `code_colonne`, `type`, `note_max`, `coefficient`, `obligatoire`, `ordre`, `date_creation`) VALUES
(1, 5, 1, 'controle continue 1', 'CC1', 'note', 20.00, 1.0, 1, 1, '2026-01-11 22:36:10'),
(2, 5, 1, 'controle continue 2', 'CC2', 'note', 20.00, 1.0, 1, 2, '2026-01-11 22:36:28'),
(3, 3, 1, 'TP1', 'TP1', 'note', 20.00, 1.0, 1, 1, '2026-01-12 00:41:17'),
(5, 3, 1, 'TP2', 'TP2', 'note', 20.00, 1.0, 1, 2, '2026-01-12 00:41:46'),
(6, 4, 1, 'controle continue 1', 'CC1', 'note', 20.00, 1.0, 1, 1, '2026-01-12 16:39:54'),
(7, 4, 1, 'controle continue 2', 'CC2', 'note', 20.00, 1.0, 1, 2, '2026-01-12 16:42:19'),
(8, 4, 1, 'exament final', 'EXF', 'note', 20.00, 1.0, 1, 3, '2026-01-12 16:45:25'),
(9, 4, 1, 'TP1', 'TP1', 'note', 20.00, 1.0, 1, 4, '2026-01-12 16:46:33'),
(10, 4, 1, 'TP2', 'TP2', 'note', 20.00, 1.0, 1, 5, '2026-01-12 16:46:44');

-- --------------------------------------------------------

--
-- Table structure for table `filieres`
--

CREATE TABLE `filieres` (
  `id` int(11) NOT NULL,
  `nom` varchar(150) NOT NULL,
  `niveau` varchar(20) DEFAULT NULL,
  `responsable_id` int(11) DEFAULT NULL,
  `date_creation` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `filieres`
--

INSERT INTO `filieres` (`id`, `nom`, `niveau`, `responsable_id`, `date_creation`) VALUES
(1, 'CS', 'Cycle Ingénieur', 3, '2026-01-11 21:55:23'),
(2, 'AI', 'Cycle Ingénieur', 4, '2026-01-11 21:56:45');

-- --------------------------------------------------------

--
-- Table structure for table `formules`
--

CREATE TABLE `formules` (
  `id` int(11) NOT NULL,
  `matiere_id` int(11) NOT NULL,
  `periode_id` int(11) NOT NULL,
  `formule` text NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `date_creation` timestamp NOT NULL DEFAULT current_timestamp(),
  `date_modification` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `formules`
--

INSERT INTO `formules` (`id`, `matiere_id`, `periode_id`, `formule`, `description`, `date_creation`, `date_modification`) VALUES
(1, 5, 1, '(MOYENNE(CC1,CC2))', NULL, '2026-01-11 22:37:53', '2026-01-11 23:06:59'),
(2, 3, 1, '(TP1 + TP2) / 2', NULL, '2026-01-12 00:42:25', '2026-01-12 00:42:25'),
(3, 4, 1, '((CC1 + CC2)*0.25 + (TP1 +TP2)*0.25 + EXF*0.5)', NULL, '2026-01-12 16:57:53', '2026-01-12 16:57:53');

-- --------------------------------------------------------

--
-- Table structure for table `historique_notes`
--

CREATE TABLE `historique_notes` (
  `id` int(11) NOT NULL,
  `note_id` int(11) NOT NULL,
  `ancienne_valeur` decimal(5,2) DEFAULT NULL,
  `nouvelle_valeur` decimal(5,2) DEFAULT NULL,
  `ancien_statut` varchar(20) DEFAULT NULL,
  `nouveau_statut` varchar(20) DEFAULT NULL,
  `modifie_par` int(11) NOT NULL,
  `motif` text DEFAULT NULL,
  `adresse_ip` varchar(45) DEFAULT NULL,
  `date_modification` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `inscriptions_matieres`
--

CREATE TABLE `inscriptions_matieres` (
  `id` int(11) NOT NULL,
  `etudiant_id` int(11) NOT NULL,
  `matiere_id` int(11) NOT NULL,
  `periode_id` int(11) NOT NULL,
  `groupe` varchar(50) DEFAULT NULL,
  `dispense` tinyint(1) DEFAULT 0,
  `date_inscription` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `inscriptions_matieres`
--

INSERT INTO `inscriptions_matieres` (`id`, `etudiant_id`, `matiere_id`, `periode_id`, `groupe`, `dispense`, `date_inscription`) VALUES
(1, 5, 5, 1, NULL, 0, '2026-01-11 22:39:31'),
(2, 6, 4, 1, NULL, 0, '2026-01-12 16:38:12'),
(3, 7, 4, 1, NULL, 0, '2026-01-12 17:25:16'),
(4, 14, 4, 1, NULL, 0, '2026-01-12 17:25:31'),
(5, 13, 4, 1, NULL, 0, '2026-01-12 17:25:50'),
(6, 11, 4, 1, NULL, 0, '2026-01-12 17:26:04'),
(7, 12, 4, 1, NULL, 0, '2026-01-12 17:26:49'),
(8, 8, 4, 1, NULL, 0, '2026-01-12 17:27:17'),
(9, 10, 4, 1, NULL, 0, '2026-01-12 17:27:33');

-- --------------------------------------------------------

--
-- Table structure for table `matieres`
--

CREATE TABLE `matieres` (
  `id` int(11) NOT NULL,
  `nom` varchar(150) NOT NULL,
  `code` varchar(50) DEFAULT NULL,
  `filiere_id` int(11) NOT NULL,
  `coefficient` decimal(3,1) DEFAULT 1.0,
  `seuil_validation` decimal(4,2) DEFAULT 10.00,
  `date_creation` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `matieres`
--

INSERT INTO `matieres` (`id`, `nom`, `code`, `filiere_id`, `coefficient`, `seuil_validation`, `date_creation`) VALUES
(1, 'Software engineering', NULL, 1, 1.0, 10.00, '2026-01-11 21:57:00'),
(3, 'Advanced Algorithmics', NULL, 1, 1.0, 10.00, '2026-01-11 21:58:33'),
(4, 'Secure Web Programming', NULL, 1, 1.0, 10.00, '2026-01-11 21:58:47'),
(5, 'Advanced Networking', NULL, 1, 1.0, 10.00, '2026-01-11 22:01:41'),
(6, 'Espagnole', NULL, 1, 1.0, 10.00, '2026-01-11 22:01:59'),
(7, 'Espagnole', NULL, 2, 1.0, 10.00, '2026-01-11 22:02:04'),
(8, 'Anglais', NULL, 1, 1.0, 10.00, '2026-01-11 22:02:27'),
(9, 'Anglais', NULL, 2, 1.0, 10.00, '2026-01-11 22:02:33');

-- --------------------------------------------------------

--
-- Table structure for table `moyennes`
--

CREATE TABLE `moyennes` (
  `id` int(11) NOT NULL,
  `etudiant_id` int(11) NOT NULL,
  `matiere_id` int(11) NOT NULL,
  `periode_id` int(11) NOT NULL,
  `moyenne` decimal(5,2) DEFAULT NULL,
  `rang` int(11) DEFAULT NULL,
  `decision` enum('valide','non_valide','rattrapage','en_attente') DEFAULT 'en_attente',
  `credits_obtenus` int(11) DEFAULT NULL,
  `date_calcul` timestamp NOT NULL DEFAULT current_timestamp(),
  `statut_validation` varchar(50) NOT NULL DEFAULT 'non_validée'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `moyennes`
--

INSERT INTO `moyennes` (`id`, `etudiant_id`, `matiere_id`, `periode_id`, `moyenne`, `rang`, `decision`, `credits_obtenus`, `date_calcul`, `statut_validation`) VALUES
(1, 5, 5, 1, 14.00, NULL, 'en_attente', NULL, '2026-01-11 23:05:12', 'non_validée'),
(2, 6, 4, 1, 18.25, NULL, 'en_attente', NULL, '2026-01-12 17:04:37', 'non_validée'),
(3, 7, 4, 1, 30.00, NULL, 'en_attente', NULL, '2026-01-12 18:03:07', 'non_validée'),
(4, 14, 4, 1, 12.75, NULL, 'en_attente', NULL, '2026-01-12 18:03:07', 'non_validée'),
(5, 13, 4, 1, 0.00, NULL, 'en_attente', NULL, '2026-01-12 18:03:07', 'non_validée'),
(6, 11, 4, 1, 21.00, NULL, 'en_attente', NULL, '2026-01-12 18:03:07', 'non_validée'),
(7, 12, 4, 1, 16.00, NULL, 'en_attente', NULL, '2026-01-12 18:03:07', 'non_validée'),
(8, 8, 4, 1, 18.25, NULL, 'en_attente', NULL, '2026-01-12 18:03:08', 'non_validée'),
(9, 10, 4, 1, 20.25, NULL, 'en_attente', NULL, '2026-01-12 18:03:08', 'non_validée');

-- --------------------------------------------------------

--
-- Table structure for table `notes`
--

CREATE TABLE `notes` (
  `id` int(11) NOT NULL,
  `etudiant_id` int(11) NOT NULL,
  `colonne_id` int(11) NOT NULL,
  `valeur` decimal(5,2) DEFAULT NULL,
  `statut` enum('saisie','absent','dispense','defaillant') DEFAULT 'saisie',
  `saisi_par` int(11) NOT NULL,
  `date_saisie` timestamp NOT NULL DEFAULT current_timestamp(),
  `date_modification` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notes`
--

INSERT INTO `notes` (`id`, `etudiant_id`, `colonne_id`, `valeur`, `statut`, `saisi_par`, `date_saisie`, `date_modification`) VALUES
(1, 5, 1, 15.00, 'saisie', 3, '2026-01-11 22:43:55', '2026-01-11 22:43:55'),
(2, 5, 2, 13.00, 'saisie', 3, '2026-01-11 22:43:55', '2026-01-11 22:43:55'),
(3, 6, 6, 14.00, 'saisie', 3, '2026-01-12 17:10:10', '2026-01-12 17:10:10'),
(4, 6, 7, 11.00, 'saisie', 3, '2026-01-12 17:10:10', '2026-01-12 17:10:10'),
(5, 6, 8, 16.00, 'saisie', 3, '2026-01-12 17:10:10', '2026-01-12 17:10:10'),
(6, 6, 9, 6.00, 'saisie', 3, '2026-01-12 17:10:10', '2026-01-12 17:10:10'),
(7, 6, 10, 10.00, 'saisie', 3, '2026-01-12 17:10:10', '2026-01-12 17:10:10'),
(8, 7, 6, 20.00, 'saisie', 3, '2026-01-12 17:28:18', '2026-01-12 17:28:18'),
(9, 7, 7, 20.00, 'saisie', 3, '2026-01-12 17:33:56', '2026-01-12 17:33:56'),
(10, 7, 8, 20.00, 'saisie', 3, '2026-01-12 17:33:56', '2026-01-12 17:33:56'),
(11, 7, 9, 20.00, 'saisie', 3, '2026-01-12 17:33:56', '2026-01-12 17:33:56'),
(12, 7, 10, 20.00, 'saisie', 3, '2026-01-12 17:33:56', '2026-01-12 17:33:56'),
(13, 14, 6, 5.00, 'saisie', 3, '2026-01-12 17:33:56', '2026-01-12 17:33:56'),
(14, 14, 7, 12.00, 'saisie', 3, '2026-01-12 17:33:56', '2026-01-12 17:33:56'),
(15, 14, 8, 17.00, 'saisie', 3, '2026-01-12 17:33:56', '2026-01-12 17:33:56'),
(16, 14, 9, 0.00, 'saisie', 3, '2026-01-12 17:33:56', '2026-01-12 17:33:56'),
(17, 14, 10, 0.00, 'saisie', 3, '2026-01-12 17:33:56', '2026-01-12 17:33:56'),
(18, 13, 6, 0.00, 'saisie', 3, '2026-01-12 17:33:56', '2026-01-12 17:33:56'),
(19, 13, 7, 0.00, 'saisie', 3, '2026-01-12 17:33:56', '2026-01-12 17:33:56'),
(20, 13, 8, 0.00, 'saisie', 3, '2026-01-12 17:33:56', '2026-01-12 17:33:56'),
(21, 13, 9, 0.00, 'saisie', 3, '2026-01-12 17:33:56', '2026-01-12 17:33:56'),
(22, 13, 10, 0.00, 'saisie', 3, '2026-01-12 17:33:56', '2026-01-12 17:33:56'),
(23, 11, 6, 18.00, 'saisie', 3, '2026-01-12 17:33:56', '2026-01-12 17:33:56'),
(24, 11, 7, 19.00, 'saisie', 3, '2026-01-12 17:33:56', '2026-01-12 17:33:56'),
(25, 11, 8, 12.00, 'saisie', 3, '2026-01-12 17:33:56', '2026-01-12 17:33:56'),
(26, 11, 9, 13.00, 'saisie', 3, '2026-01-12 17:33:56', '2026-01-12 17:33:56'),
(27, 11, 10, 10.00, 'saisie', 3, '2026-01-12 17:33:56', '2026-01-12 17:33:56'),
(28, 12, 6, 5.00, 'saisie', 3, '2026-01-12 17:33:56', '2026-01-12 17:33:56'),
(29, 12, 7, 9.00, 'saisie', 3, '2026-01-12 17:33:56', '2026-01-12 17:33:56'),
(30, 12, 8, 13.00, 'saisie', 3, '2026-01-12 17:33:56', '2026-01-12 17:33:56'),
(31, 12, 9, 6.00, 'saisie', 3, '2026-01-12 17:33:56', '2026-01-12 17:33:56'),
(32, 12, 10, 18.00, 'saisie', 3, '2026-01-12 17:33:56', '2026-01-12 17:33:56'),
(33, 8, 6, 16.00, 'saisie', 3, '2026-01-12 17:33:56', '2026-01-12 17:33:56'),
(34, 8, 7, 17.00, 'saisie', 3, '2026-01-12 17:33:56', '2026-01-12 17:33:56'),
(35, 8, 8, 15.00, 'saisie', 3, '2026-01-12 17:33:56', '2026-01-12 17:33:56'),
(36, 8, 9, 2.00, 'saisie', 3, '2026-01-12 17:33:56', '2026-01-12 17:33:56'),
(37, 8, 10, 8.00, 'saisie', 3, '2026-01-12 17:33:56', '2026-01-12 17:33:56'),
(38, 10, 6, 20.00, 'saisie', 3, '2026-01-12 17:33:56', '2026-01-12 17:33:56'),
(39, 10, 7, 13.00, 'saisie', 3, '2026-01-12 17:33:56', '2026-01-12 17:33:56'),
(40, 10, 8, 8.00, 'saisie', 3, '2026-01-12 17:33:56', '2026-01-12 17:33:56'),
(41, 10, 9, 15.00, 'saisie', 3, '2026-01-12 17:33:56', '2026-01-12 17:33:56'),
(42, 10, 10, 17.00, 'saisie', 3, '2026-01-12 17:33:56', '2026-01-12 17:33:56');

-- --------------------------------------------------------

--
-- Table structure for table `periodes`
--

CREATE TABLE `periodes` (
  `id` int(11) NOT NULL,
  `nom` varchar(100) NOT NULL,
  `annee_universitaire` varchar(9) NOT NULL,
  `type` enum('semestre','trimestre','session','rattrapage') NOT NULL,
  `date_debut_saisie` datetime NOT NULL,
  `date_fin_saisie` datetime NOT NULL,
  `statut` enum('a_venir','ouverte','fermee','publiee') DEFAULT 'a_venir',
  `date_publication` datetime DEFAULT NULL,
  `date_creation` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `periodes`
--

INSERT INTO `periodes` (`id`, `nom`, `annee_universitaire`, `type`, `date_debut_saisie`, `date_fin_saisie`, `statut`, `date_publication`, `date_creation`) VALUES
(1, 'S5', '2025-2026', 'semestre', '2025-09-01 22:40:00', '2026-01-30 22:40:00', 'publiee', NULL, '2026-01-11 21:41:09'),
(2, 'S5 Rattrapage', '2025-2026', 'rattrapage', '2026-01-30 22:41:00', '2026-06-26 22:53:00', 'a_venir', NULL, '2026-01-11 21:54:04');

-- --------------------------------------------------------

--
-- Table structure for table `progression_saisie`
--

CREATE TABLE `progression_saisie` (
  `id` int(11) NOT NULL,
  `matiere_id` int(11) NOT NULL,
  `periode_id` int(11) NOT NULL,
  `professeur_id` int(11) NOT NULL,
  `total_etudiants` int(11) NOT NULL,
  `total_notes_attendues` int(11) NOT NULL,
  `notes_saisies` int(11) DEFAULT 0,
  `pourcentage` decimal(5,2) DEFAULT 0.00,
  `valide_par_prof` tinyint(1) DEFAULT 0,
  `date_validation` datetime DEFAULT NULL,
  `date_mise_a_jour` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `progression_saisie`
--

INSERT INTO `progression_saisie` (`id`, `matiere_id`, `periode_id`, `professeur_id`, `total_etudiants`, `total_notes_attendues`, `notes_saisies`, `pourcentage`, `valide_par_prof`, `date_validation`, `date_mise_a_jour`) VALUES
(1, 5, 1, 3, 1, 2, 2, 100.00, 1, '2026-01-11 23:44:03', '2026-01-11 22:44:03'),
(2, 4, 1, 3, 8, 40, 40, 100.00, 1, '2026-01-12 18:51:47', '2026-01-12 17:51:47');

-- --------------------------------------------------------

--
-- Table structure for table `templates_formules`
--

CREATE TABLE `templates_formules` (
  `id` int(11) NOT NULL,
  `nom` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `colonnes_requises` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`colonnes_requises`)),
  `formule` text NOT NULL,
  `categorie` varchar(50) DEFAULT NULL,
  `date_creation` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `templates_formules`
--

INSERT INTO `templates_formules` (`id`, `nom`, `description`, `colonnes_requises`, `formule`, `categorie`, `date_creation`) VALUES
(1, 'Moyenne simple', 'Moyenne arithmétique de toutes les notes', '[\"Note1\", \"Note2\"]', 'MOYENNE(Note1, Note2)', 'Standard', '2026-01-11 21:27:23'),
(2, 'DS + Examen', 'DS coefficient 1, Examen coefficient 2', '[\"DS\", \"Examen\"]', '(DS + Examen * 2) / 3', 'Standard', '2026-01-11 21:27:23'),
(3, 'Meilleure des deux', 'Garde la meilleure note entre deux évaluations', '[\"Note1\", \"Note2\"]', 'MAX(Note1, Note2)', 'Spécial', '2026-01-11 21:27:23'),
(4, 'TP + Projet + Examen', 'Moyenne TP 30%, Projet 30%, Examen 40%', '[\"TP\", \"Projet\", \"Examen\"]', 'TP * 0.3 + Projet * 0.3 + Examen * 0.4', 'Standard', '2026-01-11 21:27:23');

-- --------------------------------------------------------

--
-- Table structure for table `utilisateurs`
--

CREATE TABLE `utilisateurs` (
  `id` int(11) NOT NULL,
  `nom` varchar(100) NOT NULL,
  `prenom` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `mot_de_passe` varchar(255) NOT NULL,
  `role` enum('admin','professeur','etudiant') NOT NULL,
  `date_creation` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `utilisateurs`
--

INSERT INTO `utilisateurs` (`id`, `nom`, `prenom`, `email`, `mot_de_passe`, `role`, `date_creation`) VALUES
(1, 'Admin', 'Super', 'admin@uemf.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', '2026-01-11 21:30:53'),
(3, 'sami', 'hatim', 'hatim@uemf.com', '$2y$10$rkhTyLS7bxyTSjPYyVoa.uB6rLyfg6MiE/5Ci6Oh9Or/ZccOVOaa6', 'professeur', '2026-01-11 21:55:08'),
(4, 'simo', 'simo', 'simo@uemf.com', '$2y$10$95vdf2TWTtk2Im74IOMOKOqTXyl2nL3wjWgExSH/Pp6FnxCLex2Y.', 'professeur', '2026-01-11 21:55:58'),
(5, 'ahmed', 'ahmed', 'ahmed@uemf.com', '$2y$10$YBQIXKbRKWZfIQSfb8bw2eW3ZuqdFPDItbjUFHHT8ZNXp3xWJThdS', 'etudiant', '2026-01-11 21:56:21'),
(6, 'youssef', 'sadik', 'youssef@uemf.com', '$2y$10$eOmWolo4KVk7A.THGJZdp.ygZeTrai1fj.GLx5J3GAtRuVor.YqUy', 'etudiant', '2026-01-12 16:37:33'),
(7, 'bendiouri', 'mohammed reda', 'reda@uemf.com', '$2y$10$.FF2LWFvNA.tP74lY1rgoe.ch9Pvy23gsir1uzWb6SnbM818JrPfW', 'etudiant', '2026-01-12 17:11:32'),
(8, 'sefri', 'aya', 'aya@uemf.com', '$2y$10$XvSn.JIyiPToXz7aTvK08eceoUwGSxLXk1fGJhGtrt9TOI0Tv5.9S', 'etudiant', '2026-01-12 17:11:55'),
(10, 'tikota', 'hamza', 'hamza@uemf.com', '$2y$10$o3uwwVAAEe8Ot4pA4IeywOH8QvgzwwQVEQdV11uMCR6i9FdJq7Bj6', 'etudiant', '2026-01-12 17:14:32'),
(11, 'osimhen', 'victor', 'victor@uemf.com', '$2y$10$NNvDKteb9u4Qlce0077YpOJfqLOBh9koVL.xvQL8947DiPYSl6U2W', 'etudiant', '2026-01-12 17:15:06'),
(12, 'safsaf', 'rania', 'rania@uemf.com', '$2y$10$4ubP3G179tRG56XUDsP9f.ENPZL309.8KjH86sKzg1OXHHjxE2fz6', 'etudiant', '2026-01-12 17:18:29'),
(13, 'nichan', 'alaa', 'alaa@uemf.com', '$2y$10$.SGw.v9mkGyVDWnyrN4Sx.e73HhU2pekKkAe62EBwqgbHgZSTvb7W', 'etudiant', '2026-01-12 17:19:29'),
(14, 'freda', 'chaima', 'chaima@uemf.com', '$2y$10$kpD/sATek69RkfO3Y8Tgo.CrGv7Jf3FKaqdqa83U.w9t/DZZaxSLm', 'etudiant', '2026-01-12 17:20:59');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `affectations_profs`
--
ALTER TABLE `affectations_profs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_affectation` (`professeur_id`,`matiere_id`,`periode_id`,`groupe`),
  ADD KEY `idx_affectations_prof` (`professeur_id`),
  ADD KEY `idx_affectations_matiere` (`matiere_id`),
  ADD KEY `idx_affectations_periode` (`periode_id`);

--
-- Indexes for table `configuration_colonnes`
--
ALTER TABLE `configuration_colonnes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_colonne` (`matiere_id`,`periode_id`,`code_colonne`),
  ADD KEY `periode_id` (`periode_id`),
  ADD KEY `idx_config_col_matiere_periode` (`matiere_id`,`periode_id`);

--
-- Indexes for table `filieres`
--
ALTER TABLE `filieres`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_filieres_responsable` (`responsable_id`);

--
-- Indexes for table `formules`
--
ALTER TABLE `formules`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_formule` (`matiere_id`,`periode_id`),
  ADD KEY `periode_id` (`periode_id`),
  ADD KEY `idx_formules_matiere_periode` (`matiere_id`,`periode_id`);

--
-- Indexes for table `historique_notes`
--
ALTER TABLE `historique_notes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_historique_note` (`note_id`),
  ADD KEY `idx_historique_modifie_par` (`modifie_par`);

--
-- Indexes for table `inscriptions_matieres`
--
ALTER TABLE `inscriptions_matieres`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_inscription` (`etudiant_id`,`matiere_id`,`periode_id`),
  ADD KEY `idx_inscriptions_etudiant` (`etudiant_id`),
  ADD KEY `idx_inscriptions_matiere` (`matiere_id`),
  ADD KEY `idx_inscriptions_periode` (`periode_id`);

--
-- Indexes for table `matieres`
--
ALTER TABLE `matieres`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `UQ_matieres_nom_filiere` (`nom`,`filiere_id`),
  ADD KEY `idx_matieres_filiere` (`filiere_id`);

--
-- Indexes for table `moyennes`
--
ALTER TABLE `moyennes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_moyenne` (`etudiant_id`,`matiere_id`,`periode_id`),
  ADD KEY `idx_moyennes_etudiant` (`etudiant_id`),
  ADD KEY `idx_moyennes_matiere` (`matiere_id`),
  ADD KEY `idx_moyennes_periode` (`periode_id`);

--
-- Indexes for table `notes`
--
ALTER TABLE `notes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_note` (`etudiant_id`,`colonne_id`),
  ADD KEY `saisi_par` (`saisi_par`),
  ADD KEY `idx_notes_etudiant` (`etudiant_id`),
  ADD KEY `idx_notes_colonne` (`colonne_id`);

--
-- Indexes for table `periodes`
--
ALTER TABLE `periodes`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `progression_saisie`
--
ALTER TABLE `progression_saisie`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_progression` (`matiere_id`,`periode_id`),
  ADD KEY `periode_id` (`periode_id`),
  ADD KEY `professeur_id` (`professeur_id`),
  ADD KEY `idx_progression_saisie_matiere_periode` (`matiere_id`,`periode_id`);

--
-- Indexes for table `templates_formules`
--
ALTER TABLE `templates_formules`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `utilisateurs`
--
ALTER TABLE `utilisateurs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `affectations_profs`
--
ALTER TABLE `affectations_profs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `configuration_colonnes`
--
ALTER TABLE `configuration_colonnes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `filieres`
--
ALTER TABLE `filieres`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `formules`
--
ALTER TABLE `formules`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `historique_notes`
--
ALTER TABLE `historique_notes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `inscriptions_matieres`
--
ALTER TABLE `inscriptions_matieres`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `matieres`
--
ALTER TABLE `matieres`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `moyennes`
--
ALTER TABLE `moyennes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `notes`
--
ALTER TABLE `notes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=43;

--
-- AUTO_INCREMENT for table `periodes`
--
ALTER TABLE `periodes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `progression_saisie`
--
ALTER TABLE `progression_saisie`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `templates_formules`
--
ALTER TABLE `templates_formules`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `utilisateurs`
--
ALTER TABLE `utilisateurs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `affectations_profs`
--
ALTER TABLE `affectations_profs`
  ADD CONSTRAINT `affectations_profs_ibfk_1` FOREIGN KEY (`professeur_id`) REFERENCES `utilisateurs` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `affectations_profs_ibfk_2` FOREIGN KEY (`matiere_id`) REFERENCES `matieres` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `affectations_profs_ibfk_3` FOREIGN KEY (`periode_id`) REFERENCES `periodes` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `configuration_colonnes`
--
ALTER TABLE `configuration_colonnes`
  ADD CONSTRAINT `configuration_colonnes_ibfk_1` FOREIGN KEY (`matiere_id`) REFERENCES `matieres` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `configuration_colonnes_ibfk_2` FOREIGN KEY (`periode_id`) REFERENCES `periodes` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `filieres`
--
ALTER TABLE `filieres`
  ADD CONSTRAINT `filieres_ibfk_1` FOREIGN KEY (`responsable_id`) REFERENCES `utilisateurs` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `formules`
--
ALTER TABLE `formules`
  ADD CONSTRAINT `formules_ibfk_1` FOREIGN KEY (`matiere_id`) REFERENCES `matieres` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `formules_ibfk_2` FOREIGN KEY (`periode_id`) REFERENCES `periodes` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `historique_notes`
--
ALTER TABLE `historique_notes`
  ADD CONSTRAINT `historique_notes_ibfk_1` FOREIGN KEY (`note_id`) REFERENCES `notes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `historique_notes_ibfk_2` FOREIGN KEY (`modifie_par`) REFERENCES `utilisateurs` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `inscriptions_matieres`
--
ALTER TABLE `inscriptions_matieres`
  ADD CONSTRAINT `inscriptions_matieres_ibfk_1` FOREIGN KEY (`etudiant_id`) REFERENCES `utilisateurs` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `inscriptions_matieres_ibfk_2` FOREIGN KEY (`matiere_id`) REFERENCES `matieres` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `inscriptions_matieres_ibfk_3` FOREIGN KEY (`periode_id`) REFERENCES `periodes` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `matieres`
--
ALTER TABLE `matieres`
  ADD CONSTRAINT `matieres_ibfk_1` FOREIGN KEY (`filiere_id`) REFERENCES `filieres` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `moyennes`
--
ALTER TABLE `moyennes`
  ADD CONSTRAINT `moyennes_ibfk_1` FOREIGN KEY (`etudiant_id`) REFERENCES `utilisateurs` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `moyennes_ibfk_2` FOREIGN KEY (`matiere_id`) REFERENCES `matieres` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `moyennes_ibfk_3` FOREIGN KEY (`periode_id`) REFERENCES `periodes` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `notes`
--
ALTER TABLE `notes`
  ADD CONSTRAINT `notes_ibfk_1` FOREIGN KEY (`etudiant_id`) REFERENCES `utilisateurs` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `notes_ibfk_2` FOREIGN KEY (`colonne_id`) REFERENCES `configuration_colonnes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `notes_ibfk_3` FOREIGN KEY (`saisi_par`) REFERENCES `utilisateurs` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `progression_saisie`
--
ALTER TABLE `progression_saisie`
  ADD CONSTRAINT `progression_saisie_ibfk_1` FOREIGN KEY (`matiere_id`) REFERENCES `matieres` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `progression_saisie_ibfk_2` FOREIGN KEY (`periode_id`) REFERENCES `periodes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `progression_saisie_ibfk_3` FOREIGN KEY (`professeur_id`) REFERENCES `utilisateurs` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
