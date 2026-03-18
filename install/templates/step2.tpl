{extends file="layout.tpl"}

{block name="content"}
    <h3 class="fw-bold mb-4">Base de données</h3>
    <p class="text-muted mb-4">Veuillez saisir les informations de connexion à votre base de données MySQL fournies par votre hébergeur.</p>

    <form id="dbForm">
        <div class="row g-3 mb-4">
            <div class="col-md-6">
                <label for="db_host" class="form-label fw-bold">Hôte (Host)</label>
                <input type="text" class="form-control bg-light" id="db_host" name="db_host" value="localhost" required>
                <div class="form-text">Généralement "localhost" ou "127.0.0.1".</div>
            </div>
            <div class="col-md-6">
                <label for="db_name" class="form-label fw-bold">Nom de la base</label>
                <input type="text" class="form-control" id="db_name" name="db_name" placeholder="ex: magix_cms" required>
            </div>
            <div class="col-md-6">
                <label for="db_user" class="form-label fw-bold">Utilisateur (User)</label>
                <input type="text" class="form-control" id="db_user" name="db_user" placeholder="ex: root" required>
            </div>
            <div class="col-md-6">
                <label for="db_pass" class="form-label fw-bold">Mot de passe</label>
                <input type="password" class="form-control" id="db_pass" name="db_pass">
                <div class="form-text">Laissez vide si vous n'avez pas de mot de passe (en local).</div>
            </div>
        </div>

        {* Zone d'affichage des erreurs/succès AJAX *}
        <div id="alertBox" class="alert d-none shadow-sm" role="alert"></div>

        <div class="d-flex justify-content-between mt-5 pt-3 border-top">
            <a href="index.php?step=1" class="btn btn-outline-secondary px-4 py-2">
                <i class="bi bi-arrow-left me-2"></i> Retour
            </a>
            <div>
                <button type="button" id="btnTest" class="btn btn-info text-white px-4 py-2 me-2 fw-bold shadow-sm">
                    <i class="bi bi-database-check me-1"></i> Tester la connexion
                </button>
                {* Ce bouton est caché par défaut, il s'affiche si le test est réussi *}
                <button type="submit" id="btnSave" class="btn btn-primary px-4 py-2 fw-bold shadow-sm d-none">
                    Suivant <i class="bi bi-arrow-right ms-2"></i>
                </button>
            </div>
        </div>
    </form>
{/block}

{* 🟢 SCRIPT AJAX 🟢 *}
{block name="javascripts" append}
    <script>
        {literal}
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('dbForm');
            const btnTest = document.getElementById('btnTest');
            const btnSave = document.getElementById('btnSave');
            const alertBox = document.getElementById('alertBox');

            // Fonction d'affichage des alertes
            function showAlert(message, type) {
                alertBox.className = `alert alert-${type} mt-3 d-block`;
                alertBox.innerHTML = message;
            }

            // Masque le bouton "Suivant" dès que l'utilisateur modifie un champ
            // pour l'obliger à retester la connexion
            form.addEventListener('input', function() {
                btnSave.classList.add('d-none');
                alertBox.classList.add('d-none');
            });

            // 1. ACTION : TESTER LA CONNEXION
            btnTest.addEventListener('click', function() {
                // Vérification HTML5 basique (champs requis)
                if (!form.reportValidity()) return;

                const formData = new FormData(form);
                formData.append('action', 'test'); // On indique au contrôleur ce qu'on veut faire

                // État de chargement
                btnTest.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Test...';
                btnTest.disabled = true;

                fetch('index.php?step=2', {
                    method: 'POST',
                    body: formData
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            showAlert('<i class="bi bi-check-circle-fill me-2"></i> ' + data.message, 'success');
                            btnSave.classList.remove('d-none'); // On révèle le bouton "Suivant" !
                        } else {
                            showAlert('<i class="bi bi-exclamation-triangle-fill me-2"></i> ' + data.message, 'danger');
                        }
                    })
                    .catch(error => showAlert('Erreur de communication avec le serveur.', 'danger'))
                    .finally(() => {
                        // Rétablissement du bouton
                        btnTest.innerHTML = '<i class="bi bi-database-check me-1"></i> Tester la connexion';
                        btnTest.disabled = false;
                    });
            });

            // 2. ACTION : SAUVEGARDER ET PASSER À L'ÉTAPE 3
            form.addEventListener('submit', function(e) {
                e.preventDefault();

                const formData = new FormData(form);
                formData.append('action', 'save');

                btnSave.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Création...';
                btnSave.disabled = true;

                fetch('index.php?step=2', {
                    method: 'POST',
                    body: formData
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Redirection vers l'étape de configuration du site !
                            window.location.href = 'index.php?step=3';
                        } else {
                            showAlert('<i class="bi bi-exclamation-triangle-fill me-2"></i> ' + data.message, 'danger');
                            btnSave.innerHTML = 'Suivant <i class="bi bi-arrow-right ms-2"></i>';
                            btnSave.disabled = false;
                        }
                    });
            });
        });
        {/literal}
    </script>
{/block}