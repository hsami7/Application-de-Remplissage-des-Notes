# GEMINI.md

## Project Overview

This project is a dynamic web application for managing student grades, built with PHP and a MySQL database. It allows administrators to dynamically configure grading structures, including custom columns and calculation formulas for each subject. Professors can then enter student grades based on this configuration, and students can view their published results.

The application follows a simple procedural approach without a major framework. It uses a front-controller pattern (`public/index.php`) to handle all incoming requests and route them to the appropriate action handlers or view files.

**Key Technologies:**
*   **Backend:** PHP 8+
*   **Database:** MySQL
*   **Frontend:** HTML5, CSS3, JavaScript
*   **Server:** Designed to run on a standard local server stack like XAMPP or MAMP.

**Architecture:**
*   **`public/index.php`**: Single entry point and main router. It dispatches requests to page views or action handlers based on URL parameters (`?page=` or `?action=`).
*   **`config/`**: Contains the main application and database configuration.
*   **`database/`**: Holds the SQL schema for the project.
*   **`core/`**: Contains the core application logic.
    *   **`actions/`**: PHP scripts that handle form submissions and business logic (e.g., adding a user, saving grades).
    *   **`classes/`**: Contains helper classes, notably the `FormulaParser`.
    *   **`functions/`**: Utility functions, like the database connection wrapper.
*   **`views/`**: Contains all PHP files responsible for generating HTML, separated by user role (admin, professor, student).
*   **`public/css/` and `public/js/`**: Contain the application's stylesheet and JavaScript files.

---

## Building and Running

This is a standard PHP application and does not require a build step. To run it, you need a local web server environment like XAMPP, WAMP, or MAMP.

1.  **Set up the Database:**
    *   Ensure your MySQL server is running.
    *   Create a new database (e.g., `notes_db`).
    *   Import the schema and initial data using the `database/database.sql` file.

2.  **Configure the Application:**
    *   Open `config/config.php`.
    *   Update the `DB_NAME`, `DB_USER`, and `DB_PASS` constants to match your local MySQL setup.
    *   Verify that `APP_URL` matches the URL you will use to access the project (e.g., `http://localhost/your-project-folder/public`).

3.  **Run the Server:**
    *   Place the project folder inside your web server's document root (e.g., `C:\xampp\htdocs`).
    *   Start the Apache and MySQL services from your XAMPP control panel.

4.  **Access the Application:**
    *   Open your web browser and navigate to the URL defined in your `APP_URL` config, for example: `http://localhost/Application-de-Remplissage-des-Notes/public/`

5.  **Create Initial Admin User:**
    *   The database starts with no users. To log in, you must manually create an administrator.
    *   Go to phpMyAdmin, select the `notes_db` database, click on the `utilisateurs` table, and use the "Insert" tab.
    *   **Example User:**
        *   `nom`: `Admin`
        *   `prenom`: `Super`
        *   `email`: `admin@example.com`
        *   `mot_de_passe`: (Use a hashed password. For the password "password", you can use: `$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi`)
        *   `role`: `admin`

---

## Development Conventions

*   **Routing:** The application uses a simple front controller (`public/index.php`) that checks for `?page=` and `?action=` URL parameters to decide which file to include or which function to run.
*   **Code Style:** The code is procedural, with functions grouped by role in the `core/actions/` directory. There is no strict coding standard enforced, but the code is generally readable and commented.
*   **Database:** All database interactions are performed using PHP's PDO extension. The connection logic is centralized in `core/functions/db_connect.php`.
*   **Security:**
    *   Access control is handled at the beginning of each view and action file by checking session variables (`$_SESSION['authenticated']`, `$_SESSION['user_role']`).
    *   Passwords are securely hashed using `password_hash()` and verified with `password_verify()`.
    *   The formula evaluation engine (`FormulaParser`) is implemented using a shunting-yard algorithm to avoid the use of the insecure `eval()` function.
    *   User input is escaped on output using `htmlspecialchars()` to prevent XSS attacks.
*   **Error Handling:** Basic error handling is in place, with `DEBUG_MODE` in `config.php` controlling the display of detailed error messages.
*   **Frontend:** A single stylesheet (`public/css/style.css`) contains all styles for the application. A simple JavaScript file (`public/js/main.js`) is used for client-side interactions like delete confirmations.
