{extends file="layout.tpl"}

{block name='head:title'}Images de substitution (Holders){/block}
{block name='body:id'}holder{/block}

{block name='article'}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="bi bi-images me-2"></i> Images de substitution (Holders)
        </h1>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body p-4">

            {* Explication de l'outil *}
            <div class="alert alert-info border-0 shadow-sm d-flex align-items-center mb-4">
                <i class="bi bi-info-circle fs-1 me-3 text-info"></i>
                <div>
                    <h5 class="mb-1">Générateur automatique</h5>
                    <p class="mb-0">Cet outil génère les images de remplacement par défaut pour tous les modules (Actualités, Produits, Catégories, etc.). Il utilise <strong>la couleur de fond</strong> et <strong>la taille du logo</strong> définies dans les <a href="index.php?controller=Setting" class="alert-link">paramètres généraux</a>.</p>
                </div>
            </div>

            <div class="d-flex justify-content-between align-items-center mb-4 border-bottom pb-3">
                <h2 class="h5 mb-0 text-primary">Galerie des Holders actuels</h2>

                {* Le formulaire d'action Ajax *}
                <form id="generate_holders_form" action="index.php?controller=Holder&action=generate" method="post">
                    <input type="hidden" name="hashtoken" value="{$hashtoken}">
                    <button type="submit" class="btn btn-primary px-4 shadow-sm" id="btn-generate-holders">
                        <i class="bi bi-magic me-2"></i> Générer / Actualiser les images
                    </button>
                </form>
            </div>

            {* Conteneur de la boucle *}
            <div id="holder_list_container" class="row g-3 mt-2">
                {include file="holder/loop/holders.tpl" data=$holders}
            </div>

        </div>
    </div>
{/block}

{block name="javascripts" append}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const formHolder = document.getElementById('generate_holders_form');
            const btnHolder = document.getElementById('btn-generate-holders');
            const containerHolder = document.getElementById('holder_list_container');

            if (formHolder) {
                formHolder.addEventListener('submit', function(e) {
                    e.preventDefault();

                    const originalHtml = btnHolder.innerHTML;
                    btnHolder.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span> Génération...';
                    btnHolder.disabled = true;

                    const formData = new FormData(this);

                    fetch(this.action, {
                        method: 'POST',
                        body: formData
                    })
                        .then(response => response.json())
                        .then(data => {
                            // 🔍 DÉBOGAGE : Décommentez la ligne ci-dessous si vous voulez voir la structure exacte dans la console
                            // console.log("Réponse serveur:", data);

                            if (data.success) {

                                // 🟢 CORRECTION ICI : On cherche le HTML à la racine (data.html) ou dans l'objet (data.data.html)
                                const htmlContent = data.html || (data.data && data.data.html);

                                if (htmlContent) {
                                    containerHolder.innerHTML = htmlContent;
                                } else {
                                    console.warn("Le HTML généré n'a pas été trouvé dans la réponse JSON.");
                                }

                                if (typeof MagixToast !== 'undefined') {
                                    MagixToast.success(data.message);
                                }
                            } else {
                                if (typeof MagixToast !== 'undefined') {
                                    MagixToast.error(data.message || 'Erreur lors de la génération.');
                                }
                            }
                        })
                        .catch(error => {
                            console.error('Erreur Fetch:', error);
                            // 🟢 Utilisation de MagixToast pour l'erreur HTTP (ex: 500)
                            if (typeof MagixToast !== 'undefined') {
                                MagixToast.error('Erreur serveur. Veuillez vérifier les logs.');
                            }
                        })
                        .finally(() => {
                            btnHolder.innerHTML = originalHtml;
                            btnHolder.disabled = false;
                        });
                });
            }
        });
    </script>
{/block}