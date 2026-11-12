{extends file="layout.tpl"}

{block name='head:title'}MagixClear - Nettoyage système{/block}

{block name='article'}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="bi bi-magic me-2"></i> MagixClear <span class="fs-6 text-muted fw-normal ms-2">| Nettoyage du cache et des logs</span>
        </h1>
    </div>

    <form action="index.php?controller=MagixClear&action=clear" method="post" class="validate_form">
        <input type="hidden" name="hashtoken" value="{$hashtoken}">

        <div class="row g-4">
            {* --- COLONNE FRONTEND --- *}
            <div class="col-md-6">
                <div class="card shadow-sm h-100 border-0 border-top border-4 border-primary">

                    <div class="card-header bg-white py-3 border-bottom-0 d-flex justify-content-between align-items-center">
                        <h5 class="m-0 fw-bold"><i class="bi bi-globe me-2 text-primary"></i> Site Public</h5>
                        {* BOUTONS FRONTEND *}
                        <div class="btn-group btn-group-sm">
                            <button type="button" class="btn btn-outline-primary btn-select-front" title="Tout cocher (Front)"><i class="bi bi-check-all"></i></button>
                            <button type="button" class="btn btn-outline-primary btn-unselect-front" title="Tout décocher (Front)"><i class="bi bi-x"></i></button>
                        </div>
                    </div>

                    <div class="card-body bg-light rounded-bottom">
                        {* CORRECTION : Une seule div pour le list-group *}
                        <div class="list-group list-front">

                            <label class="list-group-item d-flex justify-content-between align-items-center cursor-pointer py-3 border-start border-primary border-4">
                                <div>
                                    <div class="fw-bold">Compilation Smarty (templates_c)</div>
                                    <small class="text-muted">Fichiers pré-compilés du thème (Recommandé après modif. HTML/CSS)</small>
                                </div>
                                <div class="d-flex align-items-center">
                                    <span class="badge bg-secondary me-3 dir-size-badge" style="min-width: 65px; text-align: center;" data-target="front_tpl"><i class="bi bi-hourglass"></i> ...</span>
                                    <div class="form-check form-switch fs-4 m-0">
                                        <input class="form-check-input clear-checkbox default-checked" type="checkbox" name="targets[]" value="front_tpl" checked>
                                    </div>
                                </div>
                            </label>

                            <label class="list-group-item d-flex justify-content-between align-items-center cursor-pointer py-3 border-start border-primary border-4">
                                <div>
                                    <div class="fw-bold text-primary-emphasis"><i class="bi bi-filetype-html me-1"></i> Pages statiques (tpl_caches)</div>
                                    <small class="text-muted">Cache des vues entières générées par la "Double Barrière"</small>
                                </div>
                                <div class="d-flex align-items-center">
                                    <span class="badge bg-secondary me-3 dir-size-badge" style="min-width: 65px; text-align: center;" data-target="front_tpl_cache"><i class="bi bi-hourglass"></i> ...</span>
                                    <div class="form-check form-switch fs-4 m-0">
                                        <input class="form-check-input clear-checkbox default-checked" type="checkbox" name="targets[]" value="front_tpl_cache" checked>
                                    </div>
                                </div>
                            </label>

                            <label class="list-group-item d-flex justify-content-between align-items-center cursor-pointer py-3 border-start border-primary border-4">
                                <div>
                                    <div class="fw-bold">Données en cache (caches)</div>
                                    <small class="text-muted">Variables, résultats Smarty et divers fichiers temporaires</small>
                                </div>
                                <div class="d-flex align-items-center">
                                    <span class="badge bg-secondary me-3 dir-size-badge" style="min-width: 65px; text-align: center;" data-target="front_cache"><i class="bi bi-hourglass"></i> ...</span>
                                    <div class="form-check form-switch fs-4 m-0">
                                        <input class="form-check-input clear-checkbox default-checked" type="checkbox" name="targets[]" value="front_cache" checked>
                                    </div>
                                </div>
                            </label>

                            <label class="list-group-item d-flex justify-content-between align-items-center cursor-pointer py-3 border-start border-warning border-4">
                                <div>
                                    <div class="fw-bold text-warning-emphasis"><i class="bi bi-database-exclamation me-1"></i> Cache SQL (caches/sql)</div>
                                    <small class="text-muted">Requêtes de la BDD mises en cache</small>
                                </div>
                                <div class="d-flex align-items-center">
                                    <span class="badge bg-secondary me-3 dir-size-badge" style="min-width: 65px; text-align: center;" data-target="front_sql"><i class="bi bi-hourglass"></i> ...</span>
                                    <div class="form-check form-switch fs-4 m-0">
                                        <input class="form-check-input clear-checkbox default-checked" type="checkbox" name="targets[]" value="front_sql" checked>
                                    </div>
                                </div>
                            </label>

                            <label class="list-group-item d-flex justify-content-between align-items-center cursor-pointer py-3">
                                <div>
                                    <div class="fw-bold">Fichiers de logs (log)</div>
                                    <small class="text-muted">Historique des erreurs PHP</small>
                                </div>
                                <div class="d-flex align-items-center">
                                    <span class="badge bg-secondary me-3 dir-size-badge" style="min-width: 65px; text-align: center;" data-target="front_log"><i class="bi bi-hourglass"></i> ...</span>
                                    <div class="form-check form-switch fs-4 m-0">
                                        <input class="form-check-input clear-checkbox" type="checkbox" name="targets[]" value="front_log">
                                    </div>
                                </div>
                            </label>

                        </div> {* Fin du list-group list-front *}
                    </div>
                </div>
            </div>

            {* --- COLONNE BACKEND --- *}
            <div class="col-md-6">
                <div class="card shadow-sm h-100 border-0 border-top border-4 border-dark">

                    <div class="card-header bg-white py-3 border-bottom-0 d-flex justify-content-between align-items-center">
                        <h5 class="m-0 fw-bold"><i class="bi bi-speedometer2 me-2"></i> Administration</h5>
                        {* BOUTONS BACKEND *}
                        <div class="btn-group btn-group-sm">
                            <button type="button" class="btn btn-outline-dark btn-select-back" title="Tout cocher (Admin)"><i class="bi bi-check-all"></i></button>
                            <button type="button" class="btn btn-outline-dark btn-unselect-back" title="Tout décocher (Admin)"><i class="bi bi-x"></i></button>
                        </div>
                    </div>

                    <div class="card-body bg-light rounded-bottom">
                        {* CORRECTION : Une seule div pour le list-group *}
                        <div class="list-group list-back">

                            <label class="list-group-item d-flex justify-content-between align-items-center cursor-pointer py-3 border-start border-dark border-4">
                                <div>
                                    <div class="fw-bold">Compilation Smarty (templates_c)</div>
                                    <small class="text-muted">Fichiers pré-compilés de l'interface d'administration</small>
                                </div>
                                <div class="d-flex align-items-center">
                                    <span class="badge bg-secondary me-3 dir-size-badge" style="min-width: 65px; text-align: center;" data-target="back_tpl"><i class="bi bi-hourglass"></i> ...</span>
                                    <div class="form-check form-switch fs-4 m-0">
                                        <input class="form-check-input clear-checkbox" type="checkbox" name="targets[]" value="back_tpl">
                                    </div>
                                </div>
                            </label>

                            <label class="list-group-item d-flex justify-content-between align-items-center cursor-pointer py-3 border-start border-dark border-4">
                                <div>
                                    <div class="fw-bold text-dark-emphasis"><i class="bi bi-filetype-html me-1"></i> Pages statiques Admin (tpl_caches)</div>
                                    <small class="text-muted">Cache des vues entières générées par l'administration</small>
                                </div>
                                <div class="d-flex align-items-center">
                                    <span class="badge bg-secondary me-3 dir-size-badge" style="min-width: 65px; text-align: center;" data-target="back_tpl_cache"><i class="bi bi-hourglass"></i> ...</span>
                                    <div class="form-check form-switch fs-4 m-0">
                                        <input class="form-check-input clear-checkbox" type="checkbox" name="targets[]" value="back_tpl_cache">
                                    </div>
                                </div>
                            </label>

                            <label class="list-group-item d-flex justify-content-between align-items-center cursor-pointer py-3 border-start border-dark border-4">
                                <div>
                                    <div class="fw-bold">Données en cache (caches)</div>
                                    <small class="text-muted">Statistiques et données d'administration temporaires</small>
                                </div>
                                <div class="d-flex align-items-center">
                                    <span class="badge bg-secondary me-3 dir-size-badge" style="min-width: 65px; text-align: center;" data-target="back_cache"><i class="bi bi-hourglass"></i> ...</span>
                                    <div class="form-check form-switch fs-4 m-0">
                                        <input class="form-check-input clear-checkbox" type="checkbox" name="targets[]" value="back_cache">
                                    </div>
                                </div>
                            </label>

                            <label class="list-group-item d-flex justify-content-between align-items-center cursor-pointer py-3 border-start border-warning border-4">
                                <div>
                                    <div class="fw-bold text-warning-emphasis"><i class="bi bi-database-exclamation me-1"></i> Cache SQL Admin (caches/sql)</div>
                                    <small class="text-muted">Requêtes backend optimisées en cache</small>
                                </div>
                                <div class="d-flex align-items-center">
                                    <span class="badge bg-secondary me-3 dir-size-badge" style="min-width: 65px; text-align: center;" data-target="back_sql"><i class="bi bi-hourglass"></i> ...</span>
                                    <div class="form-check form-switch fs-4 m-0">
                                        <input class="form-check-input clear-checkbox" type="checkbox" name="targets[]" value="back_sql">
                                    </div>
                                </div>
                            </label>

                            <label class="list-group-item d-flex justify-content-between align-items-center cursor-pointer py-3">
                                <div>
                                    <div class="fw-bold">Fichiers de logs Admin (log)</div>
                                    <small class="text-muted">Historique des erreurs de l'administration</small>
                                </div>
                                <div class="d-flex align-items-center">
                                    <span class="badge bg-secondary me-3 dir-size-badge" style="min-width: 65px; text-align: center;" data-target="back_log"><i class="bi bi-hourglass"></i> ...</span>
                                    <div class="form-check form-switch fs-4 m-0">
                                        <input class="form-check-input clear-checkbox" type="checkbox" name="targets[]" value="back_log">
                                    </div>
                                </div>
                            </label>

                        </div> {* Fin du list-group list-back *}
                    </div>
                </div>
            </div>

        </div> {* Fin du row *}

        <div class="card mt-4 shadow-sm border-0">
            <div class="card-body d-flex justify-content-between align-items-center bg-white rounded">
                <div>
                    <button type="button" class="btn btn-outline-secondary btn-sm me-2" id="btn-default-all">Recommandés</button>
                </div>
                <button type="submit" class="btn btn-danger px-5 py-2 fw-bold shadow-sm">
                    <i class="bi bi-trash-fill me-2"></i> Lancer le nettoyage
                </button>
            </div>
        </div>
    </form>
{/block}

{block name="javascripts" append}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Logique des boutons de sélection (Par colonne)
            const frontCheckboxes = document.querySelectorAll('.list-front .clear-checkbox');
            const backCheckboxes = document.querySelectorAll('.list-back .clear-checkbox');
            const defaultCheckboxes = document.querySelectorAll('.default-checked');
            const allCheckboxes = document.querySelectorAll('.clear-checkbox');

            // --- ACTIONS FRONTEND ---
            document.querySelector('.btn-select-front').addEventListener('click', function() {
                frontCheckboxes.forEach(cb => cb.checked = true);
            });
            document.querySelector('.btn-unselect-front').addEventListener('click', function() {
                frontCheckboxes.forEach(cb => cb.checked = false);
            });

            // --- ACTIONS BACKEND ---
            document.querySelector('.btn-select-back').addEventListener('click', function() {
                backCheckboxes.forEach(cb => cb.checked = true);
            });
            document.querySelector('.btn-unselect-back').addEventListener('click', function() {
                backCheckboxes.forEach(cb => cb.checked = false);
            });

            // --- ACTION GLOBALE (Bouton par défaut) ---
            document.getElementById('btn-default-all').addEventListener('click', function() {
                allCheckboxes.forEach(cb => cb.checked = false);
                defaultCheckboxes.forEach(cb => cb.checked = true);
            });

            //  Récupération asynchrone des tailles de dossiers
            const formData = new FormData();
            formData.append('hashtoken', '{$hashtoken}');

            fetch('index.php?controller=MagixClear&action=getSizes', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
                .then(response => response.json())
                .then(data => {
                    if(data.status && data.sizes) {
                        document.querySelectorAll('.dir-size-badge').forEach(badge => {
                            const targetKey = badge.getAttribute('data-target');
                            if(data.sizes[targetKey]) {
                                if(data.sizes[targetKey] !== '0 B') {
                                    badge.classList.replace('bg-secondary', 'bg-info');
                                }
                                badge.innerHTML = data.sizes[targetKey];
                            } else {
                                badge.innerHTML = 'N/A';
                            }
                        });
                    }
                })
                .catch(error => {
                    console.error('Erreur lors du calcul des tailles :', error);
                    document.querySelectorAll('.dir-size-badge').forEach(badge => {
                        badge.classList.replace('bg-secondary', 'bg-danger');
                        badge.innerHTML = 'Erreur';
                    });
                });
        });
    </script>
{/block}