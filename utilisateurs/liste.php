<?php
/**
 * Page de liste des utilisateurs.
 * Réservée aux administrateurs.
 */
$page_title = "Liste des Utilisateurs";
require_once '../header.php';
require_auth("admin");

global $connexion;

$error = "";
$utilisateurs = [];

try {
    $requete = $connexion->query("SELECT id, email, role FROM utilisateurs ORDER BY id DESC");
    $utilisateurs = $requete->fetchAll();
} catch (PDOException $e) {
    $error = "Erreur lors de la récupération des utilisateurs : " . $e->getMessage();
}

?>

<h2 class="mb-4">Liste des Utilisateurs</h2>

<p><a href="ajouter.php" class="btn btn-primary">Ajouter un Utilisateur</a></p>

<?php if ($error): ?>
    <div class="alert alert-danger" role="alert">
        <strong><?= $error ?></strong>
    </div>
<?php endif; ?>

<?php if (count($utilisateurs) > 0): ?>
    <div class="table-responsive">
        <table class="table table-striped table-hover">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Email</th>
                    <th>Rôle</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($utilisateurs as $user): ?>
                    <tr>
                        <td><?= htmlspecialchars($user["id"]) ?></td>
                        <td><?= htmlspecialchars($user["email"]) ?></td>
                        <td><?= htmlspecialchars(ucfirst($user["role"])) ?></td>
                        <td>
                            <!-- Ajoutez ici des liens pour modifier ou supprimer si nécessaire -->
                            <!-- <a href="modifier.php?id=<?= $user["id"] ?>" class="btn btn-sm btn-warning">Modifier</a> -->
                            <!-- <a href="supprimer.php?id=<?= $user["id"] ?>" class="btn btn-sm btn-danger" onclick="return confirm(\'Êtes-vous sûr de vouloir supprimer cet utilisateur ?\');">Supprimer</a> -->
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php else: ?>
    <div class="alert alert-info" role="alert">
        Aucun utilisateur trouvé.
    </div>
<?php endif; ?>

<?php require_once '../footer.php'; ?>
