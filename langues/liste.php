<?php
/**
 * Page de liste des langues.
 * Accessible à tous les utilisateurs connectés.
 */
$page_title = "Liste des Langues";
require_once '../header.php';
require_auth();

global $connexion;

$error = "";
$langues = [];

try {
    $requete = $connexion->query("SELECT cod_lang, nom_lang FROM langues ORDER BY nom_lang ASC");
    $langues = $requete->fetchAll();
} catch (PDOException $e) {
    $error = "Erreur lors de la récupération des langues : " . $e->getMessage();
}

?>

<h2 class="mb-4">Liste des Langues</h2>

<?php if (in_array($_SESSION["user_role"] ?? 'guest', ['admin', 'bibliothecaire'])): ?>
    <p><a href="ajouter.php" class="btn btn-primary">Ajouter une Langue</a></p>
<?php endif; ?>

<?php if ($error): ?>
    <div class="alert alert-danger" role="alert">
        <strong><?= $error ?></strong>
    </div>
<?php endif; ?>

<?php if (count($langues) > 0): ?>
    <div class="table-responsive">
        <table class="table table-striped table-hover">
            <thead class="table-dark">
                <tr>
                    <th>Code Langue</th>
                    <th>Nom de la Langue</th>
                    <?php if (in_array($_SESSION["user_role"] ?? 'guest', ['admin', 'bibliothecaire'])): ?>
                        <th>Actions</th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($langues as $langue): ?>
                    <tr>
                        <td><?= htmlspecialchars($langue["cod_lang"]) ?></td>
                        <td><?= htmlspecialchars($langue["nom_lang"]) ?></td>
                        <?php if (in_array($_SESSION["user_role"] ?? 'guest', ['admin', 'bibliothecaire'])): ?>
                            <td>
                                <!-- Ajoutez ici des liens pour modifier ou supprimer si nécessaire -->
                                <a href="modifier.php?id=<?= $langue["cod_lang"] ?>" class="btn btn-sm btn-warning">Modifier</a>
                                <a href="supprimer.php?id=<?= $langue["cod_lang"] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette langue ?');">Supprimer</a>
                            </td>
                        <?php endif; ?>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php else: ?>
    <div class="alert alert-info" role="alert">
        Aucune langue trouvée.
        <?php if (in_array($_SESSION["user_role"] ?? 'guest', ['admin', 'bibliothecaire'])): ?>
            <a href="ajouter.php">Ajoutez-en une maintenant.</a>
        <?php endif; ?>
    </div>
<?php endif; ?>

<?php require_once '../footer.php'; ?>
