<?php
/**
 * Page de liste des domaines.
 * Accessible à tous les utilisateurs connectés.
 */
$page_title = "Liste des Domaines";
require_once '../header.php';
require_auth();

global $connexion;

$error = "";
$domaines = [];

try {
    $requete = $connexion->query("SELECT cod_Dom, nom_domaine FROM domaines ORDER BY nom_domaine ASC");
    $domaines = $requete->fetchAll();
} catch (PDOException $e) {
    $error = "Erreur lors de la récupération des domaines : " . $e->getMessage();
}

?>

<h2 class="mb-4">Liste des Domaines</h2>

<?php if (in_array($_SESSION["user_role"] ?? 'guest', ['admin', 'bibliothecaire'])): ?>
    <p><a href="ajouter.php" class="btn btn-primary">Ajouter un Domaine</a></p>
<?php endif; ?>

<?php if ($error): ?>
    <div class="alert alert-danger" role="alert">
        <strong><?= $error ?></strong>
    </div>
<?php endif; ?>

<?php if (count($domaines) > 0): ?>
    <div class="table-responsive">
        <table class="table table-striped table-hover">
            <thead class="table-dark">
                <tr>
                    <th>ID Domaine</th>
                    <th>Nom du Domaine</th>
                    <?php if (in_array($_SESSION["user_role"] ?? 'guest', ['admin', 'bibliothecaire'])): ?>
                        <th>Actions</th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($domaines as $domaine): ?>
                    <tr>
                        <td><?= htmlspecialchars($domaine["cod_Dom"]) ?></td>
                        <td><?= htmlspecialchars($domaine["nom_domaine"]) ?></td>
                        <?php if (in_array($_SESSION["user_role"] ?? 'guest', ['admin', 'bibliothecaire'])): ?>
                            <td>
                                <!-- Ajoutez ici des liens pour modifier ou supprimer si nécessaire -->
                                <a href="modifier.php?id=<?= $domaine["cod_Dom"] ?>" class="btn btn-sm btn-warning">Modifier</a>
                                <a href="supprimer.php?id=<?= $domaine["cod_Dom"] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce domaine ?');">Supprimer</a>
                            </td>
                        <?php endif; ?>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php else: ?>
    <div class="alert alert-info" role="alert">
        Aucun domaine trouvé.
        <?php if (in_array($_SESSION["user_role"] ?? 'guest', ['admin', 'bibliothecaire'])): ?>
            <a href="ajouter.php">Ajoutez-en un maintenant.</a>
        <?php endif; ?>
    </div>
<?php endif; ?>

<?php require_once '../footer.php'; ?>
