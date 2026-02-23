<?php
/**
 * En-tête de toutes les pages de l'application.
 * Inclut la connexion à la base de données, les fonctions utilitaires,
 * le début du HTML, l'intégration de Bootstrap 5 et la barre de navigation.
 */
require_once 'config.php';

// Définir le titre de la page si non défini
if (!isset($page_title)) {
    $page_title = "Bibliothèque";
}

// Récupérer le rôle de l'utilisateur connecté pour la navigation
$user_role = $_SESSION['user_role'] ?? 'guest';

// Définir les liens de navigation basés sur le rôle
$nav_links = [
    'admin' => [
        ['Tableau de Bord', 'index.php'],
        ['Utilisateurs', 'utilisateurs/liste.php'],
        ['Ouvrages', 'ouvrages/liste.php'],
        ['Auteurs', 'auteurs/liste.php'],
        ['Domaines', 'domaines/liste.php'],
        ['Langues', 'langues/liste.php'],
        ['Membres', 'membres/liste.php'],
        ['Emprunts', 'emprunts/liste.php']
    ],
    'bibliothecaire' => [
        ['Tableau de Bord', 'index.php'],
        ['Ouvrages', 'ouvrages/liste.php'],
        ['Auteurs', 'auteurs/liste.php'],
        ['Domaines', 'domaines/liste.php'],
        ['Langues', 'langues/liste.php'],
        ['Membres', 'membres/liste.php'],
        ['Emprunts', 'emprunts/liste.php']
    ],
    'membre' => [
        ['Tableau de Bord', 'index.php'],
        ['Ouvrages', 'ouvrages/liste.php'],
        ['Mes Emprunts', 'emprunts/mes_emprunts.php'] // Page spécifique pour les membres
    ],
    'guest' => [
        ['Connexion', 'login.php']
    ]
];

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($page_title) ?> - Bibliothèque</title>
    <!-- Intégration de Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Styles personnalisés -->
    <link rel="stylesheet" href="<?= $base_path ?>css/style.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="<?= $base_path ?>index.php">Bibliothèque</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <?php
                    // Afficher les liens de navigation basés sur le rôle de l'utilisateur
                    foreach (($nav_links[$user_role] ?? []) as $link) {
                        echo generate_nav_link($link[0], $link[1], $page_title);
                    }
                    ?>
                </ul>
                <ul class="navbar-nav">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <?= htmlspecialchars($_SESSION['user_email'] ?? '') ?> (<?= htmlspecialchars(ucfirst($_SESSION['user_role'] ?? 'guest')) ?>)
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                                <li><a class="dropdown-item" href="<?= $base_path ?>logout.php">Déconnexion</a></li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= $base_path ?>login.php">Connexion</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <?php display_session_messages(); // Afficher les messages de session (succès/erreur) ?>
