<?php
/**
 * Page de liste des auteurs.
 * Accessible à tous les utilisateurs connectés.
 */
$page_title = "Liste des Auteurs";
require_once '../header.php';
require_auth();

global $connexion;

$error = "";
$auteurs = [];

try {
    $requete = $connexion->query("SELECT id_auteur, nom_auteur FROM auteurs ORDER BY nom_auteur ASC");
    $auteurs = $requete->fetchAll();
} catch (PDOException $e) {
    $error = "Erreur lors de la récupération des auteurs : " . $e->getMessage();
}

?>

<h2 class="mb-4">Liste des Auteurs</h2>

<?php if (in_array($_SESSION["user_role"] ?? 'guest', ['admin', 'bibliothecaire'])): ?>
    <p><a href="ajouter.php" class="btn btn-primary">Ajouter un Auteur</a></p>
<?php endif; ?>

<?php if ($error): ?>
    <div class="alert alert-danger" role="alert">
        <strong><?= $error ?></strong>
    </div>
<?php endif; ?>

<?php if (count($auteurs) > 0): ?>
    <div class="table-responsive">
        <table class="table table-striped table-hover">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Nom de l'Auteur</th>
                    <?php if (in_array($_SESSION["user_role"] ?? 'guest', ['admin', 'bibliothecaire'])): ?>
                        <th>Actions</th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($auteurs as $auteur): ?>
                    <tr>
                        <td><?= htmlspecialchars($auteur["id_auteur"]) ?></td>
                        <td><?= htmlspecialchars($auteur["nom_auteur"]) ?></td>
                        <?php if (in_array($_SESSION["user_role"] ?? 'guest', ['admin', 'bibliothecaire'])): ?>
                            <td>
                                <!-- Ajoutez ici des liens pour modifier ou supprimer si nécessaire -->
                                <a href="modifier.php?id=<?= $auteur["id_auteur"] ?>" class="btn btn-sm btn-warning">Modifier</a>
                                <a href="supprimer.php?id=<?= $auteur["id_auteur"] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet auteur ?');">Supprimer</a>
                            </td>
                        <?php endif; ?>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php else: ?>
    <div class="alert alert-info" role="alert">
        Aucun auteur trouvé.
        <?php if (in_array($_SESSION["user_role"] ?? 'guest', ['admin', 'bibliothecaire'])): ?>
            <a href="ajouter.php">Ajoutez-en un maintenant.</a>
        <?php endif; ?>
    </div>
<?php endif; ?>

<?php require_once '../footer.php'; ?>
