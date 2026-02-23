<?php
/**
 * Script de suppression d'un ouvrage.
 * Réservé aux administrateurs et bibliothécaires.
 */
require_once '../config.php';
require_auth('bibliothecaire');

global $connexion;

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['error'] = "ID d'ouvrage invalide.";
    header('Location: liste.php');
    exit();
}

$cod_ouv = (int)$_GET['id'];

try {
    // 1. Vérifier s'il y a des emprunts en cours pour cet ouvrage
    $requete_emprunts = $connexion->prepare("SELECT COUNT(*) FROM details_emprunt WHERE cod_ouv = ? AND statut_ouvrage = 'emprunte'");
    $requete_emprunts->execute(array($cod_ouv));
    
    if ($requete_emprunts->fetchColumn() > 0) {
        $_SESSION['error'] = "Impossible de supprimer l'ouvrage. Il y a des emprunts en cours associés.";
    } else {
        // Démarrer la transaction
        $connexion->beginTransaction();

        // 2. Supprimer les liaisons ouvrage-auteur
        $connexion->prepare("DELETE FROM ouvrage_auteur WHERE cod_ouv = ?")->execute(array($cod_ouv));
        // 3. Supprimer les liaisons ouvrage-domaine
        $connexion->prepare("DELETE FROM ouvrage_domaine WHERE cod_ouv = ?")->execute(array($cod_ouv));
        // 4. Supprimer les liaisons ouvrage-langue
        $connexion->prepare("DELETE FROM ouvrage_langue WHERE cod_ouv = ?")->execute(array($cod_ouv));
        // 5. Supprimer l'ouvrage
        $requete = $connexion->prepare("DELETE FROM ouvrages WHERE cod_ouv = ?");
        $requete->execute(array($cod_ouv));
        
        $connexion->commit();
        $_SESSION['message'] = "Ouvrage supprimé avec succès.";
    }
} catch (PDOException $e) {
    if ($connexion->inTransaction()) {
        $connexion->rollBack();
    }
    $_SESSION['error'] = "Erreur lors de la suppression de l'ouvrage : " . $e->getMessage();
}

header('Location: liste.php');
exit();
