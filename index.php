<?php
/**
 * Page du tableau de bord de l'application.
 * Affiche des statistiques générales.
 */
$page_title = "Tableau de Bord";
require_once 'header.php';
require_auth(); // Nécessite une authentification

// Référence à la connexion PDO
global $connexion;

$total_ouvrages = 0;
$total_membres = 0;
$emprunts_en_cours = 0;

try {
    // Total des ouvrages (somme des nb_exemplaire)
    $requete_ouvrages = $connexion->query("SELECT SUM(nb_exemplaire) FROM ouvrages");
    $total_ouvrages = $requete_ouvrages->fetchColumn();

    // Total des membres
    $requete_membres = $connexion->query("SELECT COUNT(*) FROM membres");
    $total_membres = $requete_membres->fetchColumn();

    // Emprunts en cours (statut_ouvrage = 'emprunte')
    $requete_emprunts = $connexion->query("SELECT COUNT(*) FROM details_emprunt WHERE statut_ouvrage = 'emprunte'");
    $emprunts_en_cours = $requete_emprunts->fetchColumn();

} catch (PDOException $e) {
    $_SESSION['error'] = "Erreur lors de la récupération des statistiques : " . $e->getMessage();
}

?>

<h2 class="mb-4">Tableau de Bord</h2>

<div class="row">
    <div class="col-md-4">
        <div class="card text-white bg-primary mb-3 card-dashboard">
            <div class="card-body">
                <h5 class="card-title">Total Ouvrages</h5>
                <p class="card-text"><?= htmlspecialchars($total_ouvrages) ?></p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card text-white bg-success mb-3 card-dashboard">
            <div class="card-body">
                <h5 class="card-title">Total Membres</h5>
                <p class="card-text"><?= htmlspecialchars($total_membres) ?></p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card text-white bg-warning mb-3 card-dashboard">
            <div class="card-body">
                <h5 class="card-title">Emprunts en Cours</h5>
                <p class="card-text"><?= htmlspecialchars($emprunts_en_cours) ?></p>
            </div>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?>
