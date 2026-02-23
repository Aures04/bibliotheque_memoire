<?php
/**
 * Page de déconnexion de l'utilisateur.
 */
require_once 'config.php';

// Détruire toutes les variables de session
$_SESSION = array();

// Si vous voulez détruire complètement la session, effacez également
// le cookie de session. Notez que cela détruira la session et pas seulement les données de session !
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), 
        '', time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

// Finalement, détruire la session.
session_destroy();

$_SESSION["message"] = "Vous avez été déconnecté avec succès.";
header("Location: login.php");
exit();
?>
