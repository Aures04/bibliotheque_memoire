<?php
/**
 * Page d'ajout d'un nouveau membre.
 * Réservée aux administrateurs et bibliothécaires.
 */
$page_title = "Ajouter un Membre";
require_once '../header.php';
require_auth('bibliothecaire');

global $connexion;

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom_membre = trim($_POST['nom_membre'] ?? '');
    $email_membre = trim($_POST['email_membre'] ?? '');
    $tel_membre = trim($_POST['tel_membre'] ?? '');

    if (empty($nom_membre) || empty($email_membre)) {
        $error = "Veuillez remplir les champs obligatoires (Nom, Email).";
    } elseif (!filter_var($email_membre, FILTER_VALIDATE_EMAIL)) {
        $error = "Format d'email invalide.";
    } else {
        try {
            // Vérifier si l'email existe déjà
            $requete_check = $connexion->prepare("SELECT num_memb FROM membres WHERE email_membre = ?");
            $requete_check->execute(array($email_membre));
            if ($requete_check->rowCount() > 0) {
                $error = "Cet email est déjà utilisé par un autre membre.";
            } else {
                // Insertion du nouveau membre
                $requete = $connexion->prepare("INSERT INTO membres (nom_membre, email_membre, tel_membre) VALUES (?, ?, ?)");
                $requete->execute(array($nom_membre, $email_membre, $tel_membre));
                $_SESSION['message'] = "Membre '" . htmlspecialchars($nom_membre) . "' ajouté avec succès.";
                header('Location: liste.php');
                exit();
            }
        } catch (PDOException $e) {
            $error = "Erreur lors de l'ajout du membre : " . $e->getMessage();
        }
    }
}
?>

<h2 class="mb-4">Ajouter un Membre</h2>

<?php if ($error): ?>
    <div class="alert alert-danger" role="alert">
        <strong><?= $error ?></strong>
    </div>
<?php endif; ?>

<form method="POST">
    <div class="mb-3">
        <label for="nom_membre" class="form-label">Nom du membre :</label>
        <input type="text" class="form-control" id="nom_membre" name="nom_membre" required>
    </div>
    <div class="mb-3">
        <label for="email_membre" class="form-label">Email :</label>
        <input type="email" class="form-control" id="email_membre" name="email_membre" required>
    </div>
    <div class="mb-3">
        <label for="tel_membre" class="form-label">Téléphone :</label>
        <input type="text" class="form-control" id="tel_membre" name="tel_membre">
    </div>
    <button type="submit" class="btn btn-primary">Ajouter le Membre</button>
    <a href="liste.php" class="btn btn-secondary">Annuler</a>
</form>

<?php require_once '../footer.php'; ?>
