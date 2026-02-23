<?php
/**
 * Fichier de configuration global pour la connexion à la base de données
 * et les fonctions utilitaires.
 */

// Démarrage de la session PHP
session_start();

// =================================================================
// PARAMÈTRES DE CONNEXION À LA BASE DE DONNÉES (Conventions utilisateur)
// =================================================================
$server = "localhost";
$login = "root";
$pass = ""; // Mot de passe par défaut pour XAMPP
$dbname = "bibliotheque_db"; // Nom de la base de données à créer manuellement

// Variables pour la gestion des messages et erreurs (Conventions utilisateur)
$error = "";
$message = "";

// =================================================================
// CONNEXION À LA BASE DE DONNÉES
// =================================================================
try {
    $connexion = new PDO("mysql:host=$server;dbname=$dbname;charset=utf8", $login, $pass);
    $connexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur de connexion à la base de données : " . $e->getMessage());
}

// =================================================================
// FONCTIONS UTILITAIRES
// =================================================================

/**
 * Hache un mot de passe pour le stockage sécurisé.
 * @param string $password Le mot de passe en clair.
 * @return string Le mot de passe haché.
 */
function hash_password($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

/**
 * Vérifie un mot de passe haché.
 * @param string $password Le mot de passe en clair fourni par l'utilisateur.
 * @param string $hashed_password Le mot de passe haché stocké.
 * @return bool True si les mots de passe correspondent, False sinon.
 */
function verify_password($password, $hashed_password) {
    return password_verify($password, $hashed_password);
}

/**
 * Redirige l'utilisateur vers la page de connexion si non authentifié
 * ou si le rôle n'est pas suffisant.
 * @param string|null $required_role Le rôle minimum requis pour accéder à la page.
 */
function require_auth($required_role = null) {
    if (!isset($_SESSION["user_id"])) {
        $_SESSION["error"] = "Vous devez être connecté pour accéder à cette page.";
        header("Location: ../login.php");
        exit();
    }

    if ($required_role && $_SESSION["user_role"] !== "admin" && $_SESSION["user_role"] !== $required_role) {
        $_SESSION["error"] = "Vous n'avez pas les permissions nécessaires pour accéder à cette page.";
        header("Location: ../index.php"); // Rediriger vers le tableau de bord ou une page d'erreur
        exit();
    }
}

/**
 * Affiche les messages de succès ou d'erreur stockés en session.
 */
function display_session_messages() {
    if (isset($_SESSION["message"])) {
        echo 
            '<div class="alert alert-success alert-dismissible fade show" role="alert">
                ' . $_SESSION["message"] . '
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>';
        unset($_SESSION["message"]);
    }
    if (isset($_SESSION["error"])) {
        echo 
            '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                ' . $_SESSION["error"] . '
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>';
        unset($_SESSION["error"]);
    }
}

// Définition des rôles pour la navigation
$roles = [
    'admin' => ['Tableau de Bord', 'Utilisateurs', 'Ouvrages', 'Auteurs', 'Domaines', 'Langues', 'Membres', 'Emprunts'],
    'bibliothecaire' => ['Tableau de Bord', 'Ouvrages', 'Auteurs', 'Domaines', 'Langues', 'Membres', 'Emprunts'],
    'membre' => ['Tableau de Bord', 'Ouvrages', 'Emprunts']
];

// Chemin de base pour les liens de navigation
$base_path = '/bibliotheque/';

// Fonction pour générer les liens de navigation
function generate_nav_link($text, $path, $current_page_title) {
    global $base_path;
    $active_class = ($current_page_title === $text) ? 'active' : '';
    return '<li class="nav-item">
                <a class="nav-link ' . $active_class . '" href="' . $base_path . $path . '">' . $text . '</a>
            </li>';
}

?>
