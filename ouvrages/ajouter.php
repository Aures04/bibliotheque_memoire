<?php
/**
 * Page d'ajout d'un nouvel ouvrage.
 * Réservée aux administrateurs et bibliothécaires.
 */
$page_title = "Ajouter un Ouvrage";
require_once '../header.php';
require_auth('bibliothecaire');

global $connexion;

$error = "";
$auteurs = [];
$domaines = [];
$langues = [];

// Récupérer les listes d'auteurs, domaines et langues pour les sélecteurs
try {
    $auteurs = $connexion->query("SELECT id_auteur, nom_auteur FROM auteurs ORDER BY nom_auteur ASC")->fetchAll();
    $domaines = $connexion->query("SELECT cod_Dom, nom_domaine FROM domaines ORDER BY nom_domaine ASC")->fetchAll();
    $langues = $connexion->query("SELECT cod_lang, nom_lang FROM langues ORDER BY nom_lang ASC")->fetchAll();
} catch (PDOException $e) {
    $error = "Erreur lors de la récupération des listes : " . $e->getMessage();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$error) {
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

            // 1. Insertion de l'ouvrage
            $requete_ouvrage = $connexion->prepare("INSERT INTO ouvrages (titre, nb_exemplaire, type_ouivrage, periodicité) VALUES (?, ?, ?, ?)");
            $requete_ouvrage->execute(array($titre, $nb_exemplaire, $type_ouivrage, $periodicité));
            $cod_ouv = $connexion->lastInsertId();

            // 2. Liaison avec les auteurs
            $requete_ouvrage_auteur = $connexion->prepare("INSERT INTO ouvrage_auteur (cod_ouv, id_auteur) VALUES (?, ?)");
            foreach ($auteurs_selectionnes as $id_auteur) {
                $requete_ouvrage_auteur->execute(array($cod_ouv, $id_auteur));
            }

            // 3. Liaison avec les domaines
            $requete_ouvrage_domaine = $connexion->prepare("INSERT INTO ouvrage_domaine (cod_ouv, cod_Dom) VALUES (?, ?)");
            foreach ($domaines_selectionnes as $cod_Dom) {
                $requete_ouvrage_domaine->execute(array($cod_ouv, $cod_Dom));
            }

            // 4. Liaison avec les langues et dates de parution
            $requete_ouvrage_langue = $connexion->prepare("INSERT INTO ouvrage_langue (cod_ouv, cod_lang, date_parution) VALUES (?, ?, ?)");
            foreach ($langues_selectionnees as $cod_lang) {
                $date_parution = $dates_parution[$cod_lang] ?? null;
                if ($date_parution) {
                    $requete_ouvrage_langue->execute(array($cod_ouv, $cod_lang, $date_parution));
                }
            }

            $connexion->commit();
            $_SESSION['message'] = "Ouvrage '" . htmlspecialchars($titre) . "' ajouté avec succès.";
            header('Location: liste.php');
            exit();
        } catch (PDOException $e) {
            $connexion->rollBack();
            $error = "Erreur lors de l'ajout de l'ouvrage : " . $e->getMessage();
        }
    }
}
?>

<h2 class="mb-4">Ajouter un Ouvrage</h2>

<?php if ($error): ?>
    <div class="alert alert-danger" role="alert">
        <strong><?= $error ?></strong>
    </div>
<?php endif; ?>

<form method="POST">
    <div class="mb-3">
        <label for="titre" class="form-label">Titre :</label>
        <input type="text" class="form-control" id="titre" name="titre" required>
    </div>
    <div class="mb-3">
        <label for="nb_exemplaire" class="form-label">Nombre d'exemplaires :</label>
        <input type="number" class="form-control" id="nb_exemplaire" name="nb_exemplaire" min="1" required>
    </div>
    <div class="mb-3">
        <label for="type_ouvrage" class="form-label">Type d'ouvrage :</label>
        <select class="form-select" id="type_ouivrage" name="type_ouivrage" required>
            <option value="">-- Sélectionner un type --</option>
            <option value="livre">Livre</option>
            <option value="revue">Revue</option>
            <option value="journal">Journal</option>
        </select>
    </div>
    <div class="mb-3" id="periodicité_field" style="display: none;">
        <label for="periodicité" class="form-label">Périodicité :</label>
        <input type="text" class="form-control" id="periodicité" name="periodicité">
    </div>

    <div class="mb-3">
        <label for="auteurs" class="form-label">Auteur(s) :</label>
        <select class="form-select" id="auteurs" name="auteurs[]" multiple required>
            <?php foreach ($auteurs as $auteur): ?>
                <option value="<?= $auteur['id_auteur'] ?>"><?= htmlspecialchars($auteur['nom_auteur']) ?></option>
            <?php endforeach; ?>
        </select>
        <small class="form-text text-muted">Maintenez Ctrl (ou Cmd) pour sélectionner plusieurs auteurs.</small>
    </div>

    <div class="mb-3">
        <label for="domaines" class="form-label">Domaine(s) :</label>
        <select class="form-select" id="domaines" name="domaines[]" multiple required>
            <?php foreach ($domaines as $domaine): ?>
                <option value="<?= $domaine['cod_Dom'] ?>"><?= htmlspecialchars($domaine['nom_domaine']) ?></option>
            <?php endforeach; ?>
        </select>
        <small class="form-text text-muted">Maintenez Ctrl (ou Cmd) pour sélectionner plusieurs domaines.</small>
    </div>

    <div class="mb-3">
        <label for="langues" class="form-label">Langue(s) et Date de Parution :</label>
        <select class="form-select" id="langues" name="langues[]" multiple required>
            <?php foreach ($langues as $langue): ?>
                <option value="<?= $langue['cod_lang'] ?>"><?= htmlspecialchars($langue['nom_lang']) ?></option>
            <?php endforeach; ?>
        </select>
        <small class="form-text text-muted">Maintenez Ctrl (ou Cmd) pour sélectionner plusieurs langues.</small>
        <div id="dates_parution_container" class="mt-2"></div>
    </div>

    <button type="submit" class="btn btn-primary">Ajouter l'Ouvrage</button>
    <a href="liste.php" class="btn btn-secondary">Annuler</a>
</form>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const typeOuvrageSelect = document.getElementById('type_ouivrage');
        const periodicitéField = document.getElementById('periodicité_field');
        const languesSelect = document.getElementById('langues');
        const datesParutionContainer = document.getElementById('dates_parution_container');

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
                const div = document.createElement('div');
                div.className = 'mb-2';
                div.innerHTML = `
                    <label for="date_parution_${codeLang}" class="form-label">Date de parution pour ${nomLangue} :</label>
                    <input type="date" class="form-control" id="date_parution_${codeLang}" name="dates_parution[${codeLang}]" required>
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
