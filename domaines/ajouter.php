<?php
/**
 * Page d'ajout d'un nouveau domaine.
 * Réservée aux administrateurs et bibliothécaires.
 */
$page_title = "Ajouter un Domaine";
require_once '../header.php';
require_auth('bibliothecaire');

global $connexion;

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom_domaine = trim($_POST['nom_domaine'] ?? '');

    if (empty($nom_domaine)) {
        $error = "Veuillez saisir le nom du domaine.";
    } else {
        try {
            // Vérifier si le domaine existe déjà
            $requete_check = $connexion->prepare("SELECT cod_Dom FROM domaines WHERE nom_domaine = ?");
            $requete_check->execute(array($nom_domaine));
            if ($requete_check->rowCount() > 0) {
                $error = "Ce domaine existe déjà.";
            } else {
                // Insertion du nouveau domaine
                $requete = $connexion->prepare("INSERT INTO domaines (nom_domaine) VALUES (?)");
                $requete->execute(array($nom_domaine));
                $_SESSION['message'] = "Domaine '" . htmlspecialchars($nom_domaine) . "' ajouté avec succès.";
                header('Location: liste.php');
                exit();
            }
        } catch (PDOException $e) {
            $error = "Erreur lors de l'ajout du domaine : " . $e->getMessage();
        }
    }
}
?>

<h2 class="mb-4">Ajouter un Domaine</h2>

<?php if ($error): ?>
    <div class="alert alert-danger" role="alert">
        <strong><?= $error ?></strong>
    </div>
<?php endif; ?>

<form method="POST">
    <div class="mb-3">
        <label for="nom_domaine" class="form-label">Nom du domaine :</label>
        <input type="text" class="form-control" id="nom_domaine" name="nom_domaine" required>
    </div>
    <button type="submit" class="btn btn-primary">Ajouter le Domaine</button>
    <a href="liste.php" class="btn btn-secondary">Annuler</a>
</form>

<?php require_once '../footer.php'; ?>
