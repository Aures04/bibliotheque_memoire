<?php
/**
 * Page de modification d'un ouvrage existant.
 * Réservée aux administrateurs et bibliothécaires.
 */
$page_title = "Modifier un Ouvrage";
require_once '../header.php';
require_auth('bibliothecaire');

global $connexion;

$error = "";
$ouvrage = null;
$auteurs_disponibles = [];
$domaines_disponibles = [];
$langues_disponibles = [];
$auteurs_selectionnes_actuels = [];
$domaines_selectionnes_actuels = [];
$langues_selectionnees_actuelles = [];
$dates_parution_actuelles = [];

$cod_ouv = (int)($_GET['id'] ?? 0);

if ($cod_ouv === 0) {
    $_SESSION['error'] = "ID d'ouvrage invalide.";
    header('Location: liste.php');
    exit();
}

// =================================================================
// 1. RÉCUPÉRATION DES DONNÉES POUR LE FORMULAIRE
// =================================================================
try {
    // Ouvrage actuel
    $requete_ouvrage = $connexion->prepare("SELECT cod_ouv, titre, nb_exemplaire, type_ouivrage, périodicité FROM ouvrages WHERE cod_ouv = ?");
    $requete_ouvrage->execute(array($cod_ouv));
    $ouvrage = $requete_ouvrage->fetch();

    if (!$ouvrage) {
        $_SESSION['error'] = "Ouvrage non trouvé.";
        header('Location: liste.php');
        exit();
    }

    // Auteurs disponibles
    $auteurs_disponibles = $connexion->query("SELECT id_auteur, nom_auteur FROM auteurs ORDER BY nom_auteur ASC")->fetchAll();
    // Domaines disponibles
    $domaines_disponibles = $connexion->query("SELECT cod_Dom, nom_domaine FROM domaines ORDER BY nom_domaine ASC")->fetchAll();
    // Langues disponibles
    $langues_disponibles = $connexion->query("SELECT cod_lang, nom_lang FROM langues ORDER BY nom_lang ASC")->fetchAll();

    // Auteurs actuellement liés à l'ouvrage
    $requete_auteurs_actuels = $connexion->prepare("SELECT id_auteur FROM ouvrage_auteur WHERE cod_ouv = ?");
    $requete_auteurs_actuels->execute(array($cod_ouv));
    $auteurs_selectionnes_actuels = $requete_auteurs_actuels->fetchAll(PDO::FETCH_COLUMN);

    // Domaines actuellement liés à l'ouvrage
    $requete_domaines_actuels = $connexion->prepare("SELECT cod_Dom FROM ouvrage_domaine WHERE cod_ouv = ?");
    $requete_domaines_actuels->execute(array($cod_ouv));
    $domaines_selectionnes_actuels = $requete_domaines_actuels->fetchAll(PDO::FETCH_COLUMN);

    // Langues et dates de parution actuellement liées à l'ouvrage
    $requete_langues_actuelles = $connexion->prepare("SELECT cod_lang, date_parution FROM ouvrage_langue WHERE cod_ouv = ?");
    $requete_langues_actuelles->execute(array($cod_ouv));
    foreach ($requete_langues_actuelles->fetchAll() as $ol) {
        $langues_selectionnees_actuelles[] = $ol['cod_lang'];
        $dates_parution_actuelles[$ol['cod_lang']] = $ol['date_parution'];
    }

} catch (PDOException $e) {
    $_SESSION['error'] = "Erreur lors de la récupération des données : " . $e->getMessage();
    header('Location: liste.php');
    exit();
}

// =================================================================
// 2. TRAITEMENT DU FORMULAIRE DE MODIFICATION
// =================================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titre = trim($_POST['titre'] ?? '');
    $nb_exemplaire = (int)($_POST['nb_exemplaire'] ?? 0);
    $type_ouivrage = trim($_POST['type_ouivrage'] ?? '');
    $periodicité = ($type_ouivrage === 'revue' || $type_ouivrage === 'journal') ? trim($_POST['periodicité'] ?? '') : NULL;
    $auteurs_selectionnes = $_POST['auteurs'] ?? [];
    $domaines_selectionnes = $_POST['domaines'] ?? [];
    $langues_selectionnees = $_POST['langues'] ?? [];
    $dates_parution = $_POST['dates_parution'] ?? [];

    if (empty($titre) || $nb_exemplaire <= 0 || !in_array($type_ouivrage, ['livre', 'revue', 'journal'])) {
        $error = "Veuillez remplir tous les champs obligatoires correctement.";
    } elseif (empty($auteurs_selectionnes)) {
        $error = "Veuillez sélectionner au moins un auteur.";
    } elseif (empty($domaines_selectionnes)) {
        $error = "Veuillez sélectionner au moins un domaine.";
    } elseif (empty($langues_selectionnees)) {
        $error = "Veuillez sélectionner au moins une langue.";
    } else {
        try {
            $connexion->beginTransaction();

            // 1. Mise à jour de l'ouvrage
            $requete_update_ouvrage = $connexion->prepare("UPDATE ouvrages SET titre = ?, nb_exemplaire = ?, type_ouivrage = ?, periodicité = ? WHERE cod_ouv = ?");
            $requete_update_ouvrage->execute(array($titre, $nb_exemplaire, $type_ouivrage, $periodicité, $cod_ouv));

            // 2. Mise à jour des liaisons auteurs
            $connexion->prepare("DELETE FROM ouvrage_auteur WHERE cod_ouv = ?")->execute(array($cod_ouv));
            $requete_ouvrage_auteur = $connexion->prepare("INSERT INTO ouvrage_auteur (cod_ouv, id_auteur) VALUES (?, ?)");
            foreach ($auteurs_selectionnes as $id_auteur) {
                $requete_ouvrage_auteur->execute(array($cod_ouv, $id_auteur));
            }

            // 3. Mise à jour des liaisons domaines
            $connexion->prepare("DELETE FROM ouvrage_domaine WHERE cod_ouv = ?")->execute(array($cod_ouv));
            $requete_ouvrage_domaine = $connexion->prepare("INSERT INTO ouvrage_domaine (cod_ouv, cod_Dom) VALUES (?, ?)");
            foreach ($domaines_selectionnes as $cod_Dom) {
                $requete_ouvrage_domaine->execute(array($cod_ouv, $cod_Dom));
            }

            // 4. Mise à jour des liaisons langues et dates de parution
            $connexion->prepare("DELETE FROM ouvrage_langue WHERE cod_ouv = ?")->execute(array($cod_ouv));
            $requete_ouvrage_langue = $connexion->prepare("INSERT INTO ouvrage_langue (cod_ouv, cod_lang, date_parution) VALUES (?, ?, ?)");
            foreach ($langues_selectionnees as $cod_lang) {
                $date_parution = $dates_parution[$cod_lang] ?? null;
                if ($date_parution) {
                    $requete_ouvrage_langue->execute(array($cod_ouv, $cod_lang, $date_parution));
                }
            }

            $connexion->commit();
            $_SESSION['message'] = "Ouvrage '" . htmlspecialchars($titre) . "' modifié avec succès.";
            header('Location: liste.php');
            exit();
        } catch (PDOException $e) {
            $connexion->rollBack();
            $error = "Erreur lors de la modification de l'ouvrage : " . $e->getMessage();
        }
    }
}

?>

<h2 class="mb-4">Modifier l'Ouvrage : <?= htmlspecialchars($ouvrage['titre']) ?></h2>

<?php if ($error): ?>
    <div class="alert alert-danger" role="alert">
        <strong><?= $error ?></strong>
    </div>
<?php endif; ?>

<form method="POST">
    <div class="mb-3">
        <label for="titre" class="form-label">Titre :</label>
        <input type="text" class="form-control" id="titre" name="titre" value="<?= htmlspecialchars($ouvrage['titre']) ?>" required>
    </div>
    <div class="mb-3">
        <label for="nb_exemplaire" class="form-label">Nombre d'exemplaires :</label>
        <input type="number" class="form-control" id="nb_exemplaire" name="nb_exemplaire" min="1" value="<?= htmlspecialchars($ouvrage['nb_exemplaire']) ?>" required>
    </div>
    <div class="mb-3">
        <label for="type_ouvrage" class="form-label">Type d'ouvrage :</label>
        <select class="form-select" id="type_ouivrage" name="type_ouivrage" required>
            <option value="livre" <?= ($ouvrage['type_ouivrage'] === 'livre') ? 'selected' : '' ?>>Livre</option>
            <option value="revue" <?= ($ouvrage['type_ouivrage'] === 'revue') ? 'selected' : '' ?>>Revue</option>
            <option value="journal" <?= ($ouvrage['type_ouivrage'] === 'journal') ? 'selected' : '' ?>>Journal</option>
        </select>
    </div>
    <div class="mb-3" id="periodicité_field" style="display: <?= ($ouvrage['type_ouivrage'] === 'revue' || $ouvrage['type_ouivrage'] === 'journal') ? 'block' : 'none' ?>;">
        <label for="periodicité" class="form-label">Périodicité :</label>
        <input type="text" class="form-control" id="periodicité" name="periodicité" value="<?= htmlspecialchars($ouvrage['periodicité'] ?? '') ?>">
    </div>

    <div class="mb-3">
        <label for="auteurs" class="form-label">Auteur(s) :</label>
        <select class="form-select" id="auteurs" name="auteurs[]" multiple required>
            <?php foreach ($auteurs_disponibles as $auteur): ?>
                <option value="<?= $auteur['id_auteur'] ?>" <?= in_array($auteur['id_auteur'], $auteurs_selectionnes_actuels) ? 'selected' : '' ?>><?= htmlspecialchars($auteur['nom_auteur']) ?></option>
            <?php endforeach; ?>
        </select>
        <small class="form-text text-muted">Maintenez Ctrl (ou Cmd) pour sélectionner plusieurs auteurs.</small>
    </div>

    <div class="mb-3">
        <label for="domaines" class="form-label">Domaine(s) :</label>
        <select class="form-select" id="domaines" name="domaines[]" multiple required>
            <?php foreach ($domaines_disponibles as $domaine): ?>
                <option value="<?= $domaine['cod_Dom'] ?>" <?= in_array($domaine['cod_Dom'], $domaines_selectionnes_actuels) ? 'selected' : '' ?>><?= htmlspecialchars($domaine['nom_domaine']) ?></option>
            <?php endforeach; ?>
        </select>
        <small class="form-text text-muted">Maintenez Ctrl (ou Cmd) pour sélectionner plusieurs domaines.</small>
    </div>

    <div class="mb-3">
        <label for="langues" class="form-label">Langue(s) et Date de Parution :</label>
        <select class="form-select" id="langues" name="langues[]" multiple required>
            <?php foreach ($langues_disponibles as $langue): ?>
                <option value="<?= $langue['cod_lang'] ?>" <?= in_array($langue['cod_lang'], $langues_selectionnees_actuelles) ? 'selected' : '' ?>><?= htmlspecialchars($langue['nom_lang']) ?></option>
            <?php endforeach; ?>
        </select>
        <small class="form-text text-muted">Maintenez Ctrl (ou Cmd) pour sélectionner plusieurs langues.</small>
        <div id="dates_parution_container" class="mt-2"></div>
    </div>

    <button type="submit" class="btn btn-primary">Modifier l'Ouvrage</button>
    <a href="liste.php" class="btn btn-secondary">Annuler</a>
</form>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const typeOuvrageSelect = document.getElementById('type_ouivrage');
        const periodicitéField = document.getElementById('periodicité_field');
        const languesSelect = document.getElementById('langues');
        const datesParutionContainer = document.getElementById('dates_parution_container');
        const datesParutionActuelles = <?= json_encode($dates_parution_actuelles) ?>;

        function togglePeriodicitéField() {
            if (typeOuvrageSelect.value === 'revue' || typeOuvrageSelect.value === 'journal') {
                periodicitéField.style.display = 'block';
                periodicitéField.querySelector('input').setAttribute('required', 'required');
            } else {
                periodicitéField.style.display = 'none';
                periodicitéField.querySelector('input').removeAttribute('required');
            }
        }

        function updateDatesParutionFields() {
            datesParutionContainer.innerHTML = '';
            Array.from(languesSelect.selectedOptions).forEach(option => {
                const codeLang = option.value;
                const nomLangue = option.textContent;
                const dateActuelle = datesParutionActuelles[codeLang] || '';
                const div = document.createElement('div');
                div.className = 'mb-2';
                div.innerHTML = `
                    <label for="date_parution_${codeLang}" class="form-label">Date de parution pour ${nomLangue} :</label>
                    <input type="date" class="form-control" id="date_parution_${codeLang}" name="dates_parution[${codeLang}]" value="${dateActuelle}" required>
                `;
                datesParutionContainer.appendChild(div);
            });
        }

        typeOuvrageSelect.addEventListener('change', togglePeriodicitéField);
        languesSelect.addEventListener('change', updateDatesParutionFields);

        // Initialiser à l'ouverture de la page
        togglePeriodicitéField();
        updateDatesParutionFields();
    });
</script>

<?php require_once '../footer.php'; ?>
