<?php
/**
 * Page d'ajout d'une nouvelle langue.
 * Réservée aux administrateurs et bibliothécaires.
 */
$page_title = "Ajouter une Langue";
require_once '../header.php';
require_auth('bibliothecaire');

global $connexion;

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $code_lang = trim($_POST['code_lang'] ?? '');
    $nom_langue = trim($_POST['nom_langue'] ?? '');

    if (empty($code_lang) || empty($nom_langue)) {
        $error = "Veuillez saisir le code et le nom de la langue.";
    } elseif (strlen($code_lang) > 10) {
        $error = "Le code de la langue ne doit pas dépasser 10 caractères.";
    } else {
        try {
            // Vérifier si la langue existe déjà
            $requete_check = $connexion->prepare("SELECT code_lang FROM langues WHERE code_lang = ? OR nom_langue = ?");
            $requete_check->execute(array($code_lang, $nom_langue));
            if ($requete_check->rowCount() > 0) {
                $error = "Cette langue (code ou nom) existe déjà.";
            } else {
                // Insertion de la nouvelle langue
                $requete = $connexion->prepare("INSERT INTO langues (code_lang, nom_langue) VALUES (?, ?)");
                $requete->execute(array($code_lang, $nom_langue));
                $_SESSION['message'] = "Langue '" . htmlspecialchars($nom_langue) . "' ajoutée avec succès.";
                header('Location: liste.php');
                exit();
            }
        } catch (PDOException $e) {
            $error = "Erreur lors de l'ajout de la langue : " . $e->getMessage();
        }
    }
}
?>

<h2 class="mb-4">Ajouter une Langue</h2>

<?php if ($error): ?>
    <div class="alert alert-danger" role="alert">
        <strong><?= $error ?></strong>
    </div>
<?php endif; ?>

<form method="POST">
    <div class="mb-3">
        <label for="code_lang" class="form-label">Code de la langue (ex: fr, en) :</label>
        <input type="text" class="form-control" id="code_lang" name="code_lang" maxlength="10" required>
    </div>
    <div class="mb-3">
        <label for="nom_langue" class="form-label">Nom de la langue :</label>
        <input type="text" class="form-control" id="nom_langue" name="nom_langue" required>
    </div>
    <button type="submit" class="btn btn-primary">Ajouter la Langue</button>
    <a href="liste.php" class="btn btn-secondary">Annuler</a>
</form>

<?php require_once '../footer.php'; ?>
