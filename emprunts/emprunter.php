<?php
/**
 * Page d'enregistrement d'un nouvel emprunt.
 * Réservée aux administrateurs et bibliothécaires.
 */
$page_title = "Enregistrer un Emprunt";
require_once '../header.php';
require_auth('bibliothecaire');

global $connexion;

$error = "";
$ouvrages_disponibles = [];
$membres_disponibles = [];

// =================================================================
// 1. RÉCUPÉRATION DES DONNÉES POUR LE FORMULAIRE
// =================================================================
try {
    // Ouvrages disponibles (nb_exemplaire > 0)
    $ouvrages_disponibles = $connexion->query("SELECT cod_ouv, titre, nb_exemplaire FROM ouvrages WHERE nb_exemplaire > 0 ORDER BY titre ASC")->fetchAll();
    // Tous les membres
    $membres_disponibles = $connexion->query("SELECT num_memb, nom_membre FROM membres ORDER BY nom_membre ASC")->fetchAll();
} catch (PDOException $e) {
    $error = "Erreur lors de la récupération des données : " . $e->getMessage();
}

// =================================================================
// 2. TRAITEMENT DU FORMULAIRE D'EMPRUNT
// =================================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$error) {
    $num_memb = (int)($_POST['num_memb'] ?? 0);
    $ouvrages_selectionnes = $_POST['ouvrages'] ?? []; // Tableau des cod_ouv
    $date_emprunt = date('Y-m-d');
    $date_retour_prevue = date('Y-m-d', strtotime('+1 week')); // Une semaine après l'emprunt

    if (empty($num_memb) || empty($ouvrages_selectionnes)) {
        $error = "Veuillez sélectionner un membre et au moins un ouvrage.";
    } else {
        try {
            // Démarrer la transaction pour garantir l'intégrité des données
            $connexion->beginTransaction();

            // 1. Enregistrer l'emprunt principal
            $requete_emprunt = $connexion->prepare("INSERT INTO emprunts (num_memb, date_emprunt, date_retour_prevue) VALUES (?, ?, ?)");
            $requete_emprunt->execute(array($num_memb, $date_emprunt, $date_retour_prevue));
            $num_rmp = $connexion->lastInsertId();

            // 2. Enregistrer les détails de chaque ouvrage emprunté et mettre à jour la quantité
            $requete_detail = $connexion->prepare("INSERT INTO details_emprunt (num_emp, cod_ouv, statut_ouvrage) VALUES (?, ?, 'emprunte')");
            $requete_update_ouvrage = $connexion->prepare("UPDATE ouvrages SET nb_exemplaire = nb_exemplaire - 1 WHERE cod_ouv = ? AND nb_exemplaire > 0");

            foreach ($ouvrages_selectionnes as $cod_ouv) {
                // Vérifier la disponibilité du livre (double vérification)
                $requete_check_dispo = $connexion->prepare("SELECT nb_exemplaire FROM ouvrages WHERE cod_ouv = ? AND nb_exemplaire > 0");
                $requete_check_dispo->execute(array($cod_ouv));
                
                if ($requete_check_dispo->rowCount() > 0) {
                    $requete_detail->execute(array($num_rmp, $cod_ouv));
                    $requete_update_ouvrage->execute(array($cod_ouv));
                } else {
                    throw new Exception("L'ouvrage avec le code " . htmlspecialchars($cod_ouv) . " n'est plus disponible.");
                }
            }

            // Valider la transaction
            $connexion->commit();

            $_SESSION['message'] = "Emprunt enregistré avec succès (Numéro d'emprunt: " . $num_rmp . ").";
            header('Location: liste.php');
            exit();
        } catch (Exception $e) {
            // Annuler la transaction en cas d'erreur
            if ($connexion->inTransaction()) {
                $connexion->rollBack();
            }
            $error = "Erreur lors de l'enregistrement de l'emprunt : " . $e->getMessage();
        } catch (PDOException $e) {
            if ($connexion->inTransaction()) {
                $connexion->rollBack();
            }
            $error = "Erreur base de données lors de l'enregistrement de l'emprunt : " . $e->getMessage();
        }
    }
}
?>

<h2 class="mb-4">Enregistrer un Emprunt</h2>

<?php if ($error): ?>
    <div class="alert alert-danger" role="alert">
        <strong><?= $error ?></strong>
    </div>
<?php endif; ?>

<?php if (count($ouvrages_disponibles) > 0 && count($membres_disponibles) > 0): ?>
    <form method="POST">
        <div class="mb-3">
            <label for="num_memb" class="form-label">Membre :</label>
            <select class="form-select" id="num_memb" name="num_memb" required>
                <option value="">-- Sélectionner un membre --</option>
                <?php foreach ($membres_disponibles as $membre): ?>
                    <option value="<?= $membre['num_memb'] ?>"><?= htmlspecialchars($membre['nom_membre']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="mb-3">
            <label for="ouvrages" class="form-label">Ouvrage(s) :</label>
            <select class="form-select" id="ouvrages" name="ouvrages[]" multiple required>
                <?php foreach ($ouvrages_disponibles as $ouvrage): ?>
                    <option value="<?= $ouvrage['cod_ouv'] ?>"><?= htmlspecialchars($ouvrage['titre']) ?> (Dispo: <?= $ouvrage['nb_exemplaire'] ?>)</option>
                <?php endforeach; ?>
            </select>
            <small class="form-text text-muted">Maintenez Ctrl (ou Cmd) pour sélectionner plusieurs ouvrages.</small>
        </div>
        <button type="submit" class="btn btn-primary">Enregistrer l'Emprunt</button>
        <a href="liste.php" class="btn btn-secondary">Annuler</a>
    </form>
<?php else: ?>
    <div class="alert alert-warning" role="alert">
        Impossible d'enregistrer un emprunt. Assurez-vous d'avoir des ouvrages disponibles et des membres enregistrés.
    </div>
<?php endif; ?>

<?php require_once '../footer.php'; ?>
