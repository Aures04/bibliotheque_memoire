<?php
/**
 * Page d'ajout d'un nouvel utilisateur.
 * Réservée aux administrateurs.
 */
$page_title = "Ajouter un Utilisateur";
require_once '../header.php';
require_auth('admin');

global $connexion;

$error = "";
$message = "";

// Tente d'insérer l'utilisateur admin par défaut si la table est vide
try {
    $requete_count = $connexion->query("SELECT COUNT(*) FROM utilisateurs");
    if ($requete_count->fetchColumn() == 0) {
        $default_password_hash = hash_password('admin123');
        $requete_insert = $connexion->prepare("INSERT INTO utilisateurs (email, mot_de_passe, role) VALUES (?, ?, ?)");
        $requete_insert->execute(array('admin@example.com', $default_password_hash, 'admin'));
        $_SESSION['message'] = "Utilisateur admin par défaut (admin@example.com / admin123) créé. **N'oubliez pas de créer les tables manuellement !**";
    }
} catch (PDOException $e) {
    // Si la table n'existe pas, on affiche un message d'erreur pour guider l'utilisateur
    $error = "Erreur lors de la vérification/création de l'utilisateur admin : " . $e->getMessage() . ". Assurez-vous que la table 'utilisateurs' est créée.";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $role = trim($_POST['role'] ?? '');

    if (empty($email) || empty($password) || empty($role)) {
        $error = "Veuillez remplir tous les champs.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Format d'email invalide.";
    } elseif (!in_array($role, ['admin', 'bibliothecaire'])) {
        $error = "Rôle invalide.";
    } else {
        try {
            // Vérifier si l'email existe déjà
            $requete_check = $connexion->prepare("SELECT id FROM utilisateurs WHERE email = ?");
            $requete_check->execute(array($email));
            if ($requete_check->rowCount() > 0) {
                $error = "Cet email est déjà utilisé.";
            } else {
                // Hachage du mot de passe pour la sécurité
                $password_hash = hash_password($password);
                
                // Insertion du nouvel utilisateur
                $requete_insert = $connexion->prepare("INSERT INTO utilisateurs (email, mot_de_passe, role) VALUES (?, ?, ?)");
                $requete_insert->execute(array($email, $password_hash, $role));
                $_SESSION['message'] = "Utilisateur " . htmlspecialchars($email) . " ajouté avec succès.";
                header('Location: liste.php');
                exit();
            }
        } catch (PDOException $e) {
            $error = "Erreur lors de l'ajout de l'utilisateur : " . $e->getMessage();
        }
    }
}
?>

<?php require_once '../header.php'; ?>

<h2 class="mb-4">Ajouter un Utilisateur</h2>

<?php if ($error): ?>
    <div class="alert alert-danger" role="alert">
        <strong><?= $error ?></strong>
    </div>
<?php endif; ?>

<form method="POST">
    <div class="mb-3">
        <label for="email" class="form-label">Email :</label>
        <input type="email" class="form-control" id="email" name="email" required>
    </div>
    <div class="mb-3">
        <label for="password" class="form-label">Mot de passe :</label>
        <input type="password" class="form-control" id="password" name="password" required>
    </div>
    <div class="mb-3">
        <label for="role" class="form-label">Rôle :</label>
        <select class="form-select" id="role" name="role" required>
            <option value="">-- Sélectionner un rôle --</option>
            <option value="admin">Admin</option>
            <option value="bibliothecaire">Bibliothécaire</option>
        </select>
    </div>
    <button type="submit" class="btn btn-primary">Ajouter l'Utilisateur</button>
    <a href="liste.php" class="btn btn-secondary">Annuler</a>
</form>

<?php require_once '../footer.php'; ?>
