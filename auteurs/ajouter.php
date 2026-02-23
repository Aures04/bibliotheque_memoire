<?php
/**
 * Page d'ajout d'un nouvel auteur.
 * Réservée aux administrateurs et bibliothécaires.
 */
$page_title = "Ajouter un Auteur";
require_once '../header.php';
require_auth('bibliothecaire');

global $connexion;

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom_auteur = trim($_POST['nom_auteur'] ?? '');

    if (empty($nom_auteur)) {
        $error = "Veuillez saisir le nom de l'auteur.";
    } else {
        try {
            // Vérifier si l'auteur existe déjà
            $requete_check = $connexion->prepare("SELECT id_auteur FROM auteurs WHERE nom_auteur = ?");
            $requete_check->execute(array($nom_auteur));
            if ($requete_check->rowCount() > 0) {
                $error = "Cet auteur existe déjà.";
            } else {
                // Insertion du nouvel auteur
                $requete = $connexion->prepare("INSERT INTO auteurs (nom_auteur) VALUES (?)");
                $requete->execute(array($nom_auteur));
                $_SESSION['message'] = "Auteur '" . htmlspecialchars($nom_auteur) . "' ajouté avec succès.";
                header('Location: liste.php');
                exit();
            }
        } catch (PDOException $e) {
            $error = "Erreur lors de l'ajout de l'auteur : " . $e->getMessage();
        }
    }
}
?>

<h2 class="mb-4">Ajouter un Auteur</h2>

<?php if ($error): ?>
    <div class="alert alert-danger" role="alert">
        <strong><?= $error ?></strong>
    </div>
<?php endif; ?>

<form method="POST">
    <div class="mb-3">
        <label for="nom_auteur" class="form-label">Nom de l'auteur :</label>
        <input type="text" class="form-control" id="nom_auteur" name="nom_auteur" required>
    </div>
    <button type="submit" class="btn btn-primary">Ajouter l'Auteur</button>
    <a href="liste.php" class="btn btn-secondary">Annuler</a>
</form>

<?php require_once '../footer.php'; ?>
