# Rapport Technique du Projet : Application de Gestion des Notes

## 1. Introduction

Ce document présente un rapport technique pour l'application web de gestion des notes, une solution dynamique conçue pour les établissements d'enseignement. L'objectif principal de ce projet est de fournir une plateforme centralisée et flexible où les administrateurs peuvent configurer des structures de notation complexes, où les professeurs peuvent saisir les notes des étudiants de manière intuitive, et où les étudiants peuvent consulter leurs résultats académiques en toute simplicité.

L'application a été développée en gardant à l'esprit la modularité et la sécurité, en utilisant des technologies web éprouvées et accessibles. Elle s'adresse aux écoles, collèges et universités qui cherchent à moderniser et à rationaliser leur processus de gestion des notes, en remplaçant les systèmes manuels ou rigides par une solution adaptable à leurs besoins pédagogiques spécifiques.

### 1.1. Fonctionnalités Clés

*   **Configuration Dynamique des Notes :** Permet aux administrateurs de définir des colonnes de notes personnalisées (par exemple, "DS1", "TP", "Examen Final") pour chaque matière et chaque période, avec des coefficients et des seuils de validation spécifiques.
*   **Moteur de Calcul de Moyennes Sécurisé :** Intègre un parseur de formules personnalisé qui permet de calculer les moyennes des matières selon des formules complexes (par exemple, `(DS1 * 0.4) + (Examen * 0.6)`), sans recourir à des fonctions `eval()` non sécurisées.
*   **Gestion Basée sur les Rôles :** Implémente un système de contrôle d'accès strict avec trois rôles distincts : **Administrateur**, **Professeur** et **Étudiant**, chacun disposant de permissions et de vues adaptées à ses responsabilités.
*   **Génération de Relevés de Notes :** Offre aux étudiants la possibilité de télécharger leurs relevés de notes officiels pour une période donnée, au format PDF.
*   **Suivi de la Progression :** Permet aux administrateurs de suivre en temps réel l'avancement de la saisie des notes par les professeurs pour chaque matière.

---

## 2. Architecture du Système

### 2.1. Modèle Architectural

L'application est bâtie sur un modèle **Front Controller** simple. Toutes les requêtes HTTP sont dirigées vers un unique point d'entrée, `public/index.php`. Ce script agit comme un routeur principal qui analyse les paramètres de l'URL (`?page=` pour la navigation et `?action=` pour le traitement des formulaires) pour déterminer quelle vue afficher ou quelle logique métier exécuter.

Cette approche, bien que légère et sans dépendance à un framework externe, permet de centraliser la gestion des requêtes, la sécurité et le rendu des pages.

### 2.2. Structure des Répertoires

Le projet est organisé de manière logique pour séparer les préoccupations :

*   `/config/` : Contient les fichiers de configuration, notamment `config.php` pour les constantes de l'application et les identifiants de la base de données.
*   `/core/` : Le cœur de l'application.
    *   `actions/` : Scripts contenant les fonctions qui gèrent la logique métier (par exemple, `handle_add_user()`, `handle_save_grades()`). Chaque fichier est généralement dédié à un rôle.
    *   `classes/` : Regroupe les classes PHP, dont le `FormulaParser.php`, un composant essentiel pour l'évaluation sécurisée des formules de calcul.
    *   `functions/` : Fonctions utilitaires globales, comme la connexion à la base de données et la vérification des autorisations.
*   `/database/` : Comprend le schéma SQL (`database.sql`) et les éventuels scripts de migration pour la base de données.
*   `/public/` : La racine web de l'application. Seul ce répertoire doit être accessible publiquement par le serveur web.
    *   `index.php` : Le contrôleur frontal.
    *   `css/`, `js/` : Fichiers statiques pour la présentation et l'interactivité côté client.
*   `/views/` : Contient les fichiers de présentation (vues). Ils sont organisés en sous-dossiers par rôle (`admin`, `professeur`, `etudiant`) pour une meilleure clarté.

### 2.3. Technologies Utilisées

*   **Langage Backend :** **PHP 8+**. Choisi pour sa popularité, sa facilité de déploiement sur la plupart des serveurs web et son écosystème robuste.
*   **Base de Données :** **MySQL**. Un système de gestion de base de données relationnelle fiable, performant et largement utilisé.
*   **Frontend :** **HTML5, CSS3**. Une approche simple et sans framework pour garantir la légèreté et la compatibilité.
*   **Génération PDF :** **FPDF**. Une bibliothèque PHP simple et efficace pour la création de documents PDF, utilisée ici pour générer les relevés de notes.

---

## 3. Conception de la Base de Données

La base de données est au cœur du système. Les tables principales sont conçues pour permettre une grande flexibilité dans la gestion des notes.

*   **`utilisateurs`**: Stocke les informations des comptes pour tous les types d'utilisateurs (admin, professeur, étudiant).
*   **`periodes`**: Définit les périodes académiques (par exemple, "Semestre 1 2024-2025"). Le statut (`ouverte`, `fermee`, `publiee`) contrôle le flux de travail de la saisie des notes.
*   **`filieres`** et **`matieres`**: Permettent de définir la structure académique (par exemple, la matière "Algorithmique Avancée" appartient à la filière "Génie Informatique").
*   **`configuration_colonnes`**: Table clé de la flexibilité du système. Elle permet de définir les évaluations spécifiques (`DS1`, `TP`, etc.) pour une matière donnée dans une période donnée.
*   **`formules`**: Stocke les formules de calcul des moyennes pour chaque matière.
*   **`notes`**: Contient les notes brutes saisies par les professeurs. Chaque entrée est liée à un étudiant et à une `colonne` d'évaluation.
*   **`moyennes`**: Stocke les moyennes calculées pour chaque étudiant dans chaque matière, ainsi que la décision finale (`valide`, `non_valide`, `rattrapage`).

---

## 4. Guide d'Installation et de Déploiement

Pour exécuter cette application, un environnement de serveur web local comme XAMPP, WAMP ou MAMP est requis.

1.  **Préparation de la Base de Données :**
    *   Assurez-vous que votre service MySQL est en cours d'exécution.
    *   Créez une nouvelle base de données (par exemple, `notes_db`).
    *   Importez le fichier `database/database.sql` pour créer la structure des tables et insérer les données initiales.

2.  **Configuration de l'Application :**
    *   Ouvrez le fichier `config/config.php`.
    *   Mettez à jour les constantes `DB_NAME`, `DB_USER`, et `DB_PASS` pour correspondre à votre configuration MySQL.
    *   Assurez-vous que la constante `APP_URL` pointe vers l'URL publique de votre projet (par exemple, `http://localhost/Application-de-Remplissage-des-Notes/public`).

3.  **Déploiement sur le Serveur :**
    *   Copiez l'ensemble du projet dans le répertoire racine de votre serveur web (par exemple, `htdocs` pour XAMPP).

4.  **Création du Compte Administrateur Initial :**
    *   Le système est initialisé sans aucun utilisateur. Pour vous connecter, vous devez créer manuellement un compte administrateur.
    *   Utilisez un outil comme phpMyAdmin, sélectionnez la base de données `notes_db` et allez dans la table `utilisateurs`.
    *   Cliquez sur l'onglet "Insérer" et remplissez les champs :
        *   `nom`: `Admin`
        *   `prenom`: `Super`
        *   `email`: `admin@example.com`
        *   `mot_de_passe`: `password` (utilisez la fonction `password_hash()` de PHP pour générer un hash sécurisé si vous le souhaitez. Par exemple, pour "password", le hash est : `$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi`)
        *   `role`: `admin`

5.  **Accès à l'Application :**
    *   Ouvrez votre navigateur et accédez à l'`APP_URL` configurée. Vous devriez voir la page de connexion.

---

## 5. Considérations de Sécurité

La sécurité a été un aspect important lors du développement de l'application.

*   **Protection contre les injections SQL :** Toutes les requêtes à la base de données sont exécutées en utilisant des **requêtes préparées** avec PDO, ce qui élimine le risque d'injections SQL.
*   **Protection contre le Cross-Site Scripting (XSS) :** Toutes les données provenant de la base de données ou des utilisateurs sont systématiquement échappées à l'affichage avec la fonction `htmlspecialchars()`.
*   **Stockage Sécurisé des Mots de Passe :** Les mots de passe des utilisateurs sont hachés avec l'algorithme BCRYPT en utilisant les fonctions `password_hash()` et `password_verify()` de PHP, conformément aux meilleures pratiques.
*   **Évaluation Sécurisée des Formules :** Pour éviter les failles de sécurité liées à l'utilisation de `eval()`, un analyseur syntaxique basé sur l'algorithme **Shunting-yard** a été implémenté. Il valide et évalue les formules mathématiques dans un environnement contrôlé.
*   **Contrôle d'Accès :** Un système de contrôle d'accès basé sur les rôles est appliqué au niveau du routeur principal, garantissant que les utilisateurs ne peuvent accéder qu'aux pages et actions autorisées pour leur rôle.

---

## 6. Conclusion

L'application de gestion des notes est une solution fonctionnelle et robuste qui répond aux besoins de base de la gestion des notes dans un établissement d'enseignement. Sa flexibilité est son principal atout, permettant une adaptation facile à différents systèmes de notation.
