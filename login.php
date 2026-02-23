<?php
/**
 * Page de connexion des utilisateurs.
 */
$page_title = "Connexion";
require_once 'config.php';

// Si l'utilisateur est déjà connecté, le rediriger vers le tableau de bord
if (isset($_SESSION["user_id"])) {
    header("Location: index.php");
    exit();
}

// Initialisation des variables de message/erreur
$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($email) || empty($password)) {
        $error = "Veuillez remplir tous les champs.";
    } else {
        try {
            // Requête préparée pour récupérer l'utilisateur par email
            $requete = $connexion->prepare("SELECT id, email, mot_de_passe, role FROM utilisateurs WHERE email = ?");
            $requete->execute(array($email));
            $user = $requete->fetch();

            // Vérification du mot de passe
            if ($user && password_verify($password, $user['mot_de_passe'])) {
                // Authentification réussie
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_role'] = $user['role'];
                $_SESSION['message'] = "Connexion réussie. Bienvenue, " . htmlspecialchars($user['email']) . "!";
                header("Location: index.php");
                exit();
            } else {
                $error = "Email ou mot de passe incorrect.";
            }
        } catch (PDOException $e) {
            $error = "Erreur de connexion : " . $e->getMessage();
        }
    }
}
?>

<?php require 'header.php'; ?>

<div class="row justify-content-center">
    <div class="col-md-6">
        <h2 class="mb-4">Connexion</h2>

        <?php if ($error): ?>
            <div class="alert alert-danger" role="alert">
                <strong><?= $error ?></strong>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="mb-3">
                <label for="email" class="form-label">Email :</label>
                <input type="email" class="form-control" id="email" name="email" required>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Mot de passe :</label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>
            <button type="submit" class="btn btn-primary">Se connecter</button>
        </form>
    </div>
</div>

<?php require 'footer.php'; ?>
