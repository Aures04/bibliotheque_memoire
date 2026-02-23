<?php
/**
 * Script de gestion du retour d'un ouvrage emprunté.
 * Réservé aux administrateurs et bibliothécaires.
 */
require_once '../config.php';
require_auth('bibliothecaire');

global $connexion;

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['error'] = "ID de détail d'emprunt invalide.";
    header('Location: liste.php');
    exit();
}

$id_detail_emprunt = (int)$_GET['id'];

try {
    // 1. Récupérer les informations du détail d'emprunt
    $requete_detail = $connexion->prepare("SELECT num_emp, cod_ouv, statut_ouvrage FROM details_emprunt WHERE id_detail_emprunt = ?");
    $requete_detail->execute(array($id_detail_emprunt));
    $detail_emprunt = $requete_detail->fetch();

    if (!$detail_emprunt) {
        $_SESSION['error'] = "Détail d'emprunt non trouvé.";
    } elseif ($detail_emprunt['statut_ouvrage'] === 'retourne') {
        $_SESSION['error'] = "Cet ouvrage a déjà été retourné.";
    } else {
        // Démarrer la transaction
        $connexion->beginTransaction();

        // 2. Mettre à jour le statut du détail d'emprunt
        $requete_update_detail = $connexion->prepare("UPDATE details_emprunt SET statut_ouvrage = 'retourne', date_retour_reelle = NOW() WHERE id_detail_emprunt = ?");
        $requete_update_detail->execute(array($id_detail_emprunt));

        // 3. Mettre à jour la quantité d'ouvrages (incrémenter)
        $requete_update_ouvrage = $connexion->prepare("UPDATE ouvrages SET nb_exemplaire = nb_exemplaire + 1 WHERE code_ouv = ?");
        $requete_update_ouvrage->execute(array($detail_emprunt['code_ouv']));

        // 4. Vérifier si tous les ouvrages de l'emprunt principal sont retournés
        //    Si oui, on pourrait marquer l'emprunt principal comme 'terminé' ou 'complet'
        //    Pour l'instant, on ne fait rien sur la table 'emprunts' principal

        // Valider la transaction
        $connexion->commit();

        $_SESSION['message'] = "Ouvrage retourné avec succès.";
    }
} catch (PDOException $e) {
    // Annuler la transaction en cas d'erreur
    if ($connexion->inTransaction()) {
        $connexion->rollBack();
    }
    $_SESSION['error'] = "Erreur lors de l'enregistrement du retour : " . $e->getMessage();
}

header('Location: liste.php');
exit();
