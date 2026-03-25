{extends file="layout.tpl"}

{block name='article'}
    <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
        <h1 class="h2 mb-0"><i class="bi bi-aspect-ratio me-2 text-muted"></i> Tailles d'images globales</h1>
    </div>

    <div class="row g-4">
        {* --- COLONNE GAUCHE : FORMULAIRE D'AJOUT --- *}
        <div class="col-md-4 col-xl-3">
            <div class="card border-0 shadow-sm sticky-top" style="top: 20px;">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 fw-bold">Ajouter un format</h5>
                </div>
                <div class="card-body">
                    <form class="validate_form" action="index.php?controller=ImageConfig&action=save" method="post">
                        <input type="hidden" name="hashtoken" value="{$token}">
                        <input type="hidden" name="id_config_img" value="0">

                        <div class="mb-3">
                            <label for="module_img" class="form-label fw-semibold">Module cible <span class="text-danger">*</span></label>
                            <input type="text" class="form-control form-control-sm" id="module_img" name="module_img" placeholder="ex: catalog, pages, news" required>
                        </div>

                        <div class="mb-3">
                            <label for="attribute_img" class="form-label fw-semibold">Attribut / Entité <span class="text-danger">*</span></label>
                            <input type="text" class="form-control form-control-sm" id="attribute_img" name="attribute_img" placeholder="ex: product, category" required>
                        </div>

                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <label for="width_img" class="form-label fw-semibold">Largeur (px)</label>
                                <input type="number" class="form-control form-control-sm" id="width_img" name="width_img" min="1" required>
                            </div>
                            <div class="col-6">
                                <label for="height_img" class="form-label fw-semibold">Hauteur (px)</label>
                                <input type="number" class="form-control form-control-sm" id="height_img" name="height_img" min="1" required>
                            </div>
                        </div>

                        <div class="row g-2 mb-3">
                            <div class="col-8">
                                <label for="type_img" class="form-label fw-semibold">Nom du type</label>
                                <input type="text" class="form-control form-control-sm" id="type_img" name="type_img" placeholder="ex: small, medium..." required>
                            </div>
                            <div class="col-4">
                                <label for="prefix_img" class="form-label fw-semibold">Préfixe</label>
                                <input type="text" class="form-control form-control-sm" id="prefix_img" name="prefix_img" placeholder="ex: s, m, l" required>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="resize_img" class="form-label fw-semibold">Méthode de coupe</label>
                            <select class="form-select form-select-sm" id="resize_img" name="resize_img">
                                <option value="adaptive">Adaptive (Crop centré)</option>
                                <option value="basic">Basic (Proportionnel)</option>
                            </select>
                        </div>

                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-plus-circle me-2"></i> Créer le format
                        </button>
                    </form>
                </div>
            </div>
        </div>

        {* --- COLONNE DROITE : LISTE DES FORMATS --- *}
        <div class="col-md-8 col-xl-9">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-bold">Formats configurés</h5>
                    <span class="badge bg-primary rounded-pill">{$configs|count} tailles</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover table-striped align-middle mb-0">
                            <thead class="table-light">
                            <tr>
                                <th class="ps-3">Module</th>
                                <th>Attribut</th>
                                <th>Type (Préfixe)</th>
                                <th>Dimensions</th>
                                <th>Coupe</th>
                                <th class="text-end pe-3">Actions</th>
                            </tr>
                            </thead>
                            <tbody>
                            {if empty($configs)}
                                <tr>
                                    <td colspan="6" class="text-center py-5 text-muted">
                                        <i class="bi bi-info-circle display-6 d-block mb-3"></i>
                                        Aucune configuration d'image n'a été trouvée.
                                    </td>
                                </tr>
                            {else}
                                {foreach $configs as $cfg}
                                    <tr>
                                        <td class="ps-3 fw-bold text-dark">{$cfg.module_img}</td>
                                        <td><span class="badge bg-secondary">{$cfg.attribute_img}</span></td>
                                        <td>{$cfg.type_img} <span class="text-muted small">(-{$cfg.prefix_img})</span></td>
                                        <td>{$cfg.width_img} <i class="bi bi-x text-muted"></i> {$cfg.height_img} px</td>
                                        <td>
                                            {if $cfg.resize_img == 'adaptive'}
                                                <span class="badge bg-info text-dark"><i class="bi bi-crop"></i> Adaptive</span>
                                            {else}
                                                <span class="badge bg-light text-dark border"><i class="bi bi-arrows-angle-expand"></i> Basic</span>
                                            {/if}
                                        </td>
                                        <td class="text-end pe-3">
                                            <div class="btn-group btn-group-sm">
                                                <button type="button" class="btn btn-outline-primary btn-edit-config" data-id="{$cfg.id_config_img}">
                                                    <i class="bi bi-pencil"></i>
                                                </button>
                                                <button type="button" class="btn btn-outline-danger btn-delete-config" data-id="{$cfg.id_config_img}">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                {/foreach}
                            {/if}
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {* --- MODAL D'ÉDITION --- *}
    <div class="modal fade" id="modalEditConfig" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content border-0 shadow-sm">
                <div class="modal-header bg-light">
                    <h5 class="modal-title m-0 fw-bold"><i class="bi bi-pencil-square me-2"></i>Éditer le format</h5>
                    <button type="button" class="btn-close m-0" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <form class="validate_form" action="index.php?controller=ImageConfig&action=save" method="post">
                    <div class="modal-body">
                        <input type="hidden" name="hashtoken" value="{$token}">
                        <input type="hidden" name="id_config_img" id="edit_id_config_img">

                        <div class="row g-3 mb-3">
                            <div class="col-6">
                                <label class="form-label fw-semibold">Module</label>
                                <input type="text" class="form-control form-control-sm" name="module_img" id="edit_module_img" required>
                            </div>
                            <div class="col-6">
                                <label class="form-label fw-semibold">Attribut</label>
                                <input type="text" class="form-control form-control-sm" name="attribute_img" id="edit_attribute_img" required>
                            </div>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-6">
                                <label class="form-label fw-semibold">Largeur (px)</label>
                                <input type="number" class="form-control form-control-sm" name="width_img" id="edit_width_img" required>
                            </div>
                            <div class="col-6">
                                <label class="form-label fw-semibold">Hauteur (px)</label>
                                <input type="number" class="form-control form-control-sm" name="height_img" id="edit_height_img" required>
                            </div>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-8">
                                <label class="form-label fw-semibold">Nom du type</label>
                                <input type="text" class="form-control form-control-sm" name="type_img" id="edit_type_img" required>
                            </div>
                            <div class="col-4">
                                <label class="form-label fw-semibold">Préfixe</label>
                                <input type="text" class="form-control form-control-sm" name="prefix_img" id="edit_prefix_img" required>
                            </div>
                        </div>

                        <div class="mb-2">
                            <label class="form-label fw-semibold">Méthode de coupe</label>
                            <select class="form-select form-select-sm" name="resize_img" id="edit_resize_img">
                                <option value="adaptive">Adaptive</option>
                                <option value="basic">Basic</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer bg-light border-top-0">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-primary">Enregistrer les modifications</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {* --- MODAL DE CONFIRMATION DE SUPPRESSION --- *}
    <div class="modal fade" id="modalDeleteConfig" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-sm modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-body text-center p-4">
                    <i class="bi bi-exclamation-triangle text-danger display-4 d-block mb-3"></i>
                    <h5 class="fw-bold">Supprimer ce format ?</h5>
                    <p class="text-muted mb-4 small">Attention : Les images existantes ne seront pas supprimées, mais les nouvelles images ne seront plus redimensionnées à ce format.</p>
                    <div class="d-flex justify-content-center gap-2">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Annuler</button>
                        <button type="button" class="btn btn-danger" id="btnConfirmDeleteConfig">Oui, supprimer</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="batch-progress-modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Mise à jour des images en cours</h5>
                </div>
                <div class="modal-body text-center">
                    <p class="mb-3 text-muted">Veuillez patienter, ne fermez pas cette page.</p>

                    <div class="progress mb-2" style="height: 25px;">
                        <div id="batch-progress-bar" class="progress-bar bg-primary progress-bar-striped" role="progressbar" style="width: 0%;"></div>
                    </div>
                    <div id="batch-progress-text" class="fw-bold">Initialisation...</div>

                </div>
            </div>
        </div>
    </div>
{/block}

{block name='javascripts' append}
    <script>
        {literal}
        document.addEventListener('DOMContentLoaded', function() {
            // On récupère le token depuis le champ caché généré en HTML
            const token = document.querySelector('input[name="hashtoken"]').value;

            // ÉDITION
            const editModal = new bootstrap.Modal(document.getElementById('modalEditConfig'));
            document.querySelectorAll('.btn-edit-config').forEach(btn => {
                btn.addEventListener('click', function() {
                    const id = this.getAttribute('data-id');

                    // CORRECTION : Utilisation de la concaténation classique
                    fetch('index.php?controller=ImageConfig&action=edit&id=' + id)
                        .then(response => response.json())
                        .then(res => {
                            if(res.status && res.data) {
                                document.getElementById('edit_id_config_img').value = res.data.id_config_img;
                                document.getElementById('edit_module_img').value = res.data.module_img;
                                document.getElementById('edit_attribute_img').value = res.data.attribute_img;
                                document.getElementById('edit_width_img').value = parseInt(res.data.width_img);
                                document.getElementById('edit_height_img').value = parseInt(res.data.height_img);
                                document.getElementById('edit_type_img').value = res.data.type_img;
                                document.getElementById('edit_prefix_img').value = res.data.prefix_img;
                                document.getElementById('edit_resize_img').value = res.data.resize_img;
                                editModal.show();
                            }
                        });
                });
            });

            // SUPPRESSION
            const deleteModal = new bootstrap.Modal(document.getElementById('modalDeleteConfig'));
            let currentDeleteId = null;

            document.querySelectorAll('.btn-delete-config').forEach(btn => {
                btn.addEventListener('click', function() {
                    currentDeleteId = this.getAttribute('data-id');
                    deleteModal.show();
                });
            });

            document.getElementById('btnConfirmDeleteConfig').addEventListener('click', function() {
                if(!currentDeleteId) return;

                const formData = new FormData();
                formData.append('id', currentDeleteId);
                // Utilisation de la variable JS token
                formData.append('hashtoken', token);

                fetch('index.php?controller=ImageConfig&action=delete', {
                    method: 'POST',
                    body: formData
                })
                    .then(response => response.json())
                    .then(res => {
                        if(res.status || res.success) {
                            window.location.reload();
                        } else {
                            alert(res.message);
                        }
                    });
            });
        });
        {/literal}
    </script>
{/block}