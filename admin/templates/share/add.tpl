{extends file="layout.tpl"}
{block name='head:title'}Ajouter un réseau{/block}
{block name='body:id'}share{/block}

{block name='article'}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="bi bi-share me-2"></i> Ajouter un réseau
        </h1>
        <a href="index.php?controller=Share" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left"></i> Retour à la liste
        </a>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body p-4">

            <form action="index.php?controller=Share&action=add" method="post" class="validate_form add_form">
                <input type="hidden" name="hashtoken" value="{$hashtoken}">

                <div class="row mb-4 bg-light p-3 rounded border align-items-center">

                    {* --- NOM DU RESEAU --- *}
                    <div class="col-md-3 mb-3 mb-md-0">
                        <label for="name" class="form-label fw-medium">Nom du réseau <span class="text-danger">*</span></label>
                        <input type="text" id="name" name="name" class="form-control" placeholder="ex: Facebook" required>
                    </div>

                    {* --- CLASSE DE L'ICÔNE --- *}
                    <div class="col-md-3 mb-3 mb-md-0">
                        <label for="icon" class="form-label fw-medium">Icône Bootstrap <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text"><i id="icon-preview" class="bi bi-hash"></i></span>
                            <input type="text" id="icon" name="icon" class="form-control" placeholder="bi-facebook" required>
                        </div>
                    </div>

                    {* --- ORDRE --- *}
                    <div class="col-md-2 mb-3 mb-md-0 text-center">
                        <label for="order_share" class="form-label fw-medium d-block">Ordre</label>
                        <input type="number" id="order_share" name="order_share" class="form-control text-center" value="0" min="0">
                    </div>

                    {* --- ACTIF --- *}
                    <div class="col-md-4 text-center">
                        <label class="form-label fw-medium d-block">Actif</label>
                        <div class="form-check form-switch fs-5 mt-1 d-inline-block">
                            <input class="form-check-input" type="checkbox" role="switch" name="is_active" value="1" checked>
                        </div>
                    </div>
                </div>

                {* --- URL DE PARTAGE --- *}
                <div class="row mb-4">
                    <div class="col-12">
                        <label for="url_share" class="form-label fw-medium">URL de partage <span class="text-danger">*</span></label>
                        <input type="url" id="url_share" name="url_share" class="form-control font-monospace" placeholder="ex: https://www.facebook.com/sharer/sharer.php?u=%URL%" required>
                        <div class="form-text text-muted mt-2">
                            Utilisez les variables <strong>%URL%</strong> et <strong>%NAME%</strong>. Elles seront remplacées automatiquement par le CMS lors du clic.
                        </div>
                    </div>
                </div>

                <hr class="my-4">
                <div class="d-flex justify-content-end">
                    <button type="submit" name="action" value="add" class="btn btn-success px-5">
                        <i class="bi bi-plus-circle me-2"></i> Ajouter le réseau
                    </button>
                </div>
            </form>

        </div>
    </div>
{/block}

{block name="javascripts" append}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Petit script pour prévisualiser l'icône Bootstrap en temps réel
            const inputIcon = document.getElementById('icon');
            const iconPreview = document.getElementById('icon-preview');

            if (inputIcon && iconPreview) {
                inputIcon.addEventListener('input', function() {
                    let val = this.value.trim();
                    iconPreview.className = val !== '' ? 'bi ' + val : 'bi bi-hash';
                });
            }
        });
    </script>
{/block}