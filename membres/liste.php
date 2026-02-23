<?php
/**
 * Page de liste des membres.
 * Accessible à tous les utilisateurs connectés.
 */
$page_title = "Liste des Membres";
require_once '../header.php';
require_auth();

global $connexion;

$error = "";
$membres = [];

try {
    $requete = $connexion->query("SELECT num_memb, nom_membre, email_membre, tel_membre FROM membres ORDER BY nom_membre ASC");
    $membres = $requete->fetchAll();
} catch (PDOException $e) {
    $error = "Erreur lors de la récupération des membres : " . $e->getMessage();
}

?>

<h2 class="mb-4">Liste des Membres</h2>

<?php if (in_array($_SESSION["user_role"] ?? 'guest', ['admin', 'bibliothecaire'])): ?>
    <p><a href="ajouter.php" class="btn btn-primary">Ajouter un Membre</a></p>
<?php endif; ?>

<?php if ($error): ?>
    <div class="alert alert-danger" role="alert">
        <strong><?= $error ?></strong>
    </div>
<?php endif; ?>

<?php if (count($membres) > 0): ?>
    <div class="table-responsive">
        <table class="table table-striped table-hover">
            <thead class="table-dark">
                <tr>
                    <th>Numéro Membre</th>
                    <th>Nom</th>
                    <th>Email</th>
                    <th>Téléphone</th>
                    <?php if (in_array($_SESSION["user_role"] ?? 'guest', ['admin', 'bibliothecaire'])): ?>
                        <th>Actions</th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($membres as $membre): ?>
                    <tr>
                        <td><?= htmlspecialchars($membre["num_memb"]) ?></td>
                        <td><?= htmlspecialchars($membre["nom_membre"]) ?></td>
                        <td><?= htmlspecialchars($membre["email_membre"]) ?></td>
                        <td><?= htmlspecialchars($membre["tel_membre"]) ?></td>
                        <?php if (in_array($_SESSION["user_role"] ?? 'guest', ['admin', 'bibliothecaire'])): ?>
                            <td>
                                <!-- Ajoutez ici des liens pour modifier ou supprimer si nécessaire -->
                                <!-- <a href="modifier.php?id=<?= $membre["num_memb"] ?>" class="btn btn-sm btn-warning">Modifier</a> -->
                                <!-- <a href="supprimer.php?id=<?= $membre["num_memb"] ?>" class="btn btn-sm btn-danger" onclick="return confirm(\'Êtes-vous sûr de vouloir supprimer ce membre ?\');">Supprimer</a> -->
                            </td>
                        <?php endif; ?>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php else: ?>
    <div class="alert alert-info" role="alert">
        Aucun membre trouvé.
        <?php if (in_array($_SESSION["user_role"] ?? 'guest', ['admin', 'bibliothecaire'])): ?>
            <a href="ajouter.php">Ajoutez-en un maintenant.</a>
        <?php endif; ?>
    </div>
<?php endif; ?>

<?php require_once '../footer.php'; ?>
