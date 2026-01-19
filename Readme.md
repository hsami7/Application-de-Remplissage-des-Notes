# PLATEFORME DE GESTION DES NOTES

## 1. PRÉSENTATION DU PROJET
Ce projet consiste en la conception et le développement d'une solution web centralisée pour la digitalisation du processus de gestion des notes. L'objectif principal est de fournir une plateforme centralisée et flexible où les administrateurs peuvent configurer des structures de notation complexes, où les professeurs peuvent saisir les notes des étudiants de manière intuitive, et où les étudiants peuvent consulter leurs résultats académiques en toute simplicité.

### Stack Technique
*   **Langage Backend :** **PHP 8+**
*   **Base de Données :** **MySQL**
*   **Frontend :** **HTML5, CSS3**
*   **Génération PDF :** **FPDF**

## 2. ÉQUIPE DE DÉVELOPPEMENT & RÔLES
| Membre                  | Module Responsable                                                                 |
|-------------------------|------------------------------------------------------------------------------------|
| Hatim Sami              | Architecture Globale, Sécurité, Infra, Modules Admin & Configuration (Main)        |
| Ahmed Mouhib            | Espace Professeur & Algorithmes de notation                                        |
| ILYASS MOATASSIM        | Espace Logistique & Génération de documents                                        |
| Youssef Sadik           | Espace Étudiant & Tableaux de bord (KPI)                                           |
| Mohammed reda bendiouri | Intégration et Tests                                                               |

## 3. DÉTAIL DES CONTRIBUTIONS
### 3.1. ARCHITECTURE & MODULES ADMIN (Hatim Sami)
*   **Architecture Applicative & Modules**: Conception du modèle **Front Controller**, où toutes les requêtes sont dirigées vers un point d'entrée unique (`public/index.php`). Organisation du projet en répertoires logiques (`/config`, `/core`, `/database`, `/public`, `/views`) pour une séparation claire des préoccupations.
*   **Modules Admin & Configuration**: Développement des fonctionnalités clés pour l'administrateur, notamment la **configuration dynamique des notes**, qui permet de définir des colonnes de notes personnalisées (par exemple, "DS1", "TP", "Examen Final") avec des coefficients et des seuils de validation spécifiques pour chaque matière.
*   **Sécurité & Infrastructure**: Implémentation des mesures de sécurité fondamentales, incluant la protection contre les injections SQL via des **requêtes préparées** (PDO), la prévention des failles XSS par l'échappement systématique des données (`htmlspecialchars`), et le stockage sécurisé des mots de passe avec l'algorithme **BCRYPT**.

### 3.2. ESPACE PROFESSEUR (Ahmed Mouhib)
*   **Espace Professeur**: Développement de l'interface permettant aux professeurs de saisir les notes des étudiants. Cette interface affiche dynamiquement les colonnes de notes configurées par l'administrateur pour chaque matière.
*   **Algorithmes de notation**: Intégration du **moteur de calcul de moyennes sécurisé**. Ce module utilise un parseur de formules personnalisé pour évaluer des expressions mathématiques complexes, garantissant l'intégrité des calculs sans recourir à la fonction non sécurisée `eval()`.

### 3.3. ESPACE LOGISTIQUE & DOCUMENTS (ILYASS MOATASSIM)
*   **Génération de Documents**: Intégration de la bibliothèque **FPDF** pour permettre aux étudiants de télécharger leurs relevés de notes officiels au format PDF.
*   **Espace Logistique**: Gestion de la structure de la base de données, y compris les tables pour les utilisateurs, les périodes, les filières, les matières, et la configuration des colonnes de notes, assurant la flexibilité et la persistance des données.

### 3.4. ESPACE ÉTUDIANT & KPI (Youssef Sadik)
*   **Espace Étudiant**: Développement de l'interface où les étudiants peuvent consulter leurs notes et leurs moyennes une fois qu'elles sont publiées par l'administration.
*   **Tableaux de bord (KPI)**: Mise en place du système de suivi de la progression, permettant aux administrateurs de visualiser en temps réel l'avancement de la saisie des notes par les professeurs pour chaque matière.

### 3.5. INTÉGRATION & TESTS (Mohammed reda bendiouri)
*   **Intégration Continue**: Assurer l'intégration harmonieuse des différents modules développés par l'équipe, en veillant à la cohérence de l'ensemble de l'application.
*   **Tests et Validation**: Responsable de la phase de test, incluant la validation du flux de travail des différents rôles (administrateur, professeur, étudiant) et la vérification de la robustesse de l'application.

## 4. INSTALLATION LOCALE
Pour exécuter cette application, un environnement de serveur web local comme XAMPP, WAMP ou MAMP est requis.

1.  **Cloner le dépôt :**
    ```bash
    git clone https://github.com/Hatim-Sami/Application-de-Remplissage-des-Notes.git
    ```

2.  **Base de Données :**
    *   Assurez-vous que votre service MySQL est en cours d'exécution.
    *   Créez une nouvelle base de données (par exemple, `notes_db`).
    *   Importez le fichier `database/notes_db.sql` pour créer la structure des tables et insérer les données initiales.

3.  **Configuration :**
    *   Ouvrez le fichier `config/config.php`.
    *   Mettez à jour les constantes `DB_NAME`, `DB_USER`, et `DB_PASS` pour correspondre à votre configuration MySQL.
    *   Assurez-vous que la constante `APP_URL` pointe vers l'URL publique de votre projet (par exemple, `http://localhost/Application-de-Remplissage-des-Notes/public`).

4.  **Lancement :**
    *   Copiez l'ensemble du projet dans le répertoire racine de votre serveur web (par exemple, `htdocs` pour XAMPP).
    *   Ouvrez votre navigateur et accédez à l'`APP_URL` configurée.

### Création du Compte Administrateur Initial :
*   Le système est initialisé sans aucun utilisateur. Pour vous connecter, vous devez créer manuellement un compte administrateur via un outil comme phpMyAdmin.
*   **Table**: `utilisateurs`
*   **Champs**:
    *   `nom`: `Admin`
    *   `prenom`: `Super`
    *   `email`: `admin@example.com`
    *   `mot_de_passe`: `password` (Le hash BCRYPT pour "password" est : `$2a$12$dNqSwSXB4kQczie2Q4mQMuvKstxAgsiER350oW0cr0MpYeWsXwMzu`)
    *   `role`: `admin`
