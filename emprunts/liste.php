<?php
/**
 * Page de liste des emprunts.
 * Accessible à tous les utilisateurs connectés.
 */
$page_title = "Liste des Emprunts";
require_once '../header.php';
require_auth();

global $connexion;

$error = "";
$emprunts = [];

try {
    // Récupération de tous les emprunts avec les noms des ouvrages et des membres
    $sql = "SELECT 
                de.id_detail_emprunt, 
                e.num_rmp, 
                o.titre AS ouvrage_titre, 
                m.nom_membre, 
                e.date_emprunt, 
                e.date_retour_prevue, 
                de.date_retour_reelle, 
                de.statut_ouvrage
            FROM 
                details_emprunt de
            JOIN 
                emprunts e ON de.num_emp = e.num_rmp
            JOIN 
                ouvrages o ON de.cod_ouv = o.cod_ouv
            JOIN 
                membres m ON e.num_memb = m.num_memb
            ORDER BY 
                e.date_emprunt DESC, de.id_detail_emprunt DESC";
    $requete = $connexion->query($sql);
    $emprunts = $requete->fetchAll();
} catch (PDOException $e) {
    $error = "Erreur lors de la récupération des emprunts : " . $e->getMessage();
}
?>

<h2 class="mb-4">Liste des Emprunts</h2>

<?php if (in_array($_SESSION["user_role"] ?? 'guest', ['admin', 'bibliothecaire'])): ?>
    <p><a href="emprunter.php" class="btn btn-primary">Enregistrer un nouvel Emprunt</a></p>
<?php endif; ?>

<?php if ($error): ?>
    <div class="alert alert-danger" role="alert">
        <strong><?= $error ?></strong>
    </div>
<?php endif; ?>

<?php if (count($emprunts) > 0): ?>
    <div class="table-responsive">
        <table class="table table-striped table-hover">
            <thead class="table-dark">
                <tr>
                    <th>ID Détail</th>
                    <th>Num Emprunt</th>
                    <th>Ouvrage</th>
                    <th>Membre</th>
                    <th>Date Emprunt</th>
                    <th>Retour Prévu</th>
                    <th>Retour Réel</th>
                    <th>Statut</th>
                    <?php if (in_array($_SESSION["user_role"] ?? 'guest', ['admin', 'bibliothecaire'])): ?>
                        <th>Action</th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($emprunts as $emprunt): ?>
                    <tr>
                        <td><?= htmlspecialchars($emprunt['id_detail_emprunt']) ?></td>
                        <td><?= htmlspecialchars($emprunt['num_rmp']) ?></td>
                        <td><?= htmlspecialchars($emprunt['ouvrage_titre']) ?></td>
                        <td><?= htmlspecialchars($emprunt['nom_membre']) ?></td>
                        <td><?= htmlspecialchars($emprunt['date_emprunt']) ?></td>
                        <td><?= htmlspecialchars($emprunt['date_retour_prevue']) ?></td>
                        <td><?= $emprunt['date_retour_reelle'] ? htmlspecialchars($emprunt['date_retour_reelle']) : 'N/A' ?></td>
                        <td><span class="badge bg-<?= $emprunt['statut_ouvrage'] === 'emprunte' ? 'warning' : ($emprunt['statut_ouvrage'] === 'retourne' ? 'success' : 'danger') ?>"><?= htmlspecialchars(ucfirst($emprunt['statut_ouvrage'])) ?></span></td>
                        <?php if (in_array($_SESSION["user_role"] ?? 'guest', ['admin', 'bibliothecaire'])): ?>
                            <td>
                                <?php if ($emprunt['statut_ouvrage'] === 'emprunte'): ?>
                                    <a href="retourner.php?id=<?= $emprunt['id_detail_emprunt'] ?>" class="btn btn-sm btn-success" onclick="return confirm('Confirmer le retour de cet ouvrage ?');">Retourner</a>
                                <?php else: ?>
                                    <span class="text-muted">Terminé</span>
                                <?php endif; ?>
                            </td>
                        <?php endif; ?>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php else: ?>
    <div class="alert alert-info" role="alert">
        Aucun emprunt enregistré.
    </div>
<?php endif; ?>

<?php require_once '../footer.php'; ?>
