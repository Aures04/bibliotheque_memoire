<?php
/**
 * Page de liste des ouvrages.
 * Accessible à tous les utilisateurs connectés.
 */
$page_title = "Liste des Ouvrages";
require_once '../header.php';
require_auth();

global $connexion;

$error = "";
$ouvrages = [];

try {
    // Récupération de tous les ouvrages avec leurs auteurs, domaines et langues
    $sql = "SELECT 
                o.cod_ouv, 
                o.titre, 
                o.nb_exemplaire, 
                o.type_ouivrage, 
                o.periodicité,
                GROUP_CONCAT(DISTINCT a.nom_auteur SEPARATOR ', ') AS auteurs,
                GROUP_CONCAT(DISTINCT d.nom_domaine SEPARATOR ', ') AS domaines,
                GROUP_CONCAT(DISTINCT l.nom_lang SEPARATOR ', ') AS langues
            FROM 
                ouvrages o
            LEFT JOIN 
                ouvrage_auteur oa ON o.cod_ouv = oa.cod_ouv
            LEFT JOIN 
                auteurs a ON oa.id_auteur = a.id_auteur
            LEFT JOIN 
                ouvrage_domaine od ON o.cod_ouv = od.cod_ouv
            LEFT JOIN 
                domaines d ON od.cod_Dom = d.cod_Dom
            LEFT JOIN 
                ouvrage_langue ol ON o.cod_ouv = ol.cod_ouv
            LEFT JOIN 
                langues l ON ol.cod_lang = l.cod_lang
            GROUP BY 
                o.cod_ouv
            ORDER BY 
                o.titre ASC";
    $requete = $connexion->query($sql);
    if ($requete->rowCount() > 0) {
        $ouvrages = $requete->fetchAll();
    }
} catch (PDOException $e) {
    $error = "Erreur lors de la récupération des ouvrages : " . $e->getMessage();
}

?>

<h2 class="mb-4">Liste des Ouvrages</h2>

<?php if (in_array($_SESSION["user_role"] ?? 'guest', ['admin', 'bibliothecaire'])): ?>
    <p><a href="ajouter.php" class="btn btn-primary">Ajouter un Ouvrage</a></p>
<?php endif; ?>

<?php if ($error): ?>
    <div class="alert alert-danger" role="alert">
        <strong><?= $error ?></strong>
    </div>
<?php endif; ?>

<?php if (!empty($ouvrages)): ?>
    <div class="table-responsive">
        <table class="table table-striped table-hover">
            <thead class="table-dark">
                <tr>
                    <th>Code</th>
                    <th>Titre</th>
                    <th>Exemplaires</th>
                    <th>Type</th>
                    <th>Périodicité</th>
                    <th>Auteur(s)</th>
                    <th>Domaine(s)</th>
                    <th>Langue(s)</th>
                    <?php if (in_array($_SESSION["user_role"] ?? 'guest', ['admin', 'bibliothecaire'])): ?>
                        <th>Actions</th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($ouvrages as $ouvrage): ?>
                    <tr>
                        <td><?= htmlspecialchars($ouvrage['cod_ouv']) ?></td>
                        <td><?= htmlspecialchars($ouvrage['titre']) ?></td>
                        <td><?= htmlspecialchars($ouvrage['nb_exemplaire']) ?></td>
                        <td><?= htmlspecialchars(ucfirst($ouvrage['type_ouivrage'])) ?></td>
                        <td><?= htmlspecialchars($ouvrage['periodicité'] ?? 'N/A') ?></td>
                        <td><?= htmlspecialchars($ouvrage['auteurs'] ?? 'N/A') ?></td>
                        <td><?= htmlspecialchars($ouvrage['domaines'] ?? 'N/A') ?></td>
                        <td><?= htmlspecialchars($ouvrage['langues'] ?? 'N/A') ?></td>
                        <?php if (in_array($_SESSION["user_role"] ?? 'guest', ['admin', 'bibliothecaire'])): ?>
                            <td>
                                <a href="modifier.php?id=<?= $ouvrage['cod_ouv'] ?>" class="btn btn-sm btn-warning">Modifier</a>
                                <a href="supprimer.php?id=<?= $ouvrage['cod_ouv'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet ouvrage ?');">Supprimer</a>
                            </td>
                        <?php endif; ?>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php else: ?>
    <div class="alert alert-info" role="alert">
        Aucun ouvrage trouvé.
        <?php if (in_array($_SESSION["user_role"] ?? 'guest', ['admin', 'bibliothecaire'])): ?>
            <a href="ajouter.php">Ajoutez-en un maintenant.</a>
        <?php endif; ?>
    </div>
<?php endif; ?>

<?php require_once '../footer.php'; ?>
