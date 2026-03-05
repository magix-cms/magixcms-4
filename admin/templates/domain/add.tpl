{extends file="layout.tpl"}

{block name='head:title'}{#add_domain#|ucfirst}{/block}

{block name='article'}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="bi bi-globe me-2"></i> {#add_domain#|ucfirst}
        </h1>
        <a href="index.php?controller=Domain" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left"></i> Retour à la liste
        </a>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body p-4">

            <form id="add_domain_form" action="index.php?controller=Domain&action=add" method="post" class="validate_form add_form">
                <input type="hidden" name="hashtoken" value="{$hashtoken}">

                {* 1. BLOC DE STRUCTURE : URL et Paramètres Globaux *}
                <div class="row mb-4 bg-light p-3 rounded border">
                    <div class="col-md-6 mb-3 mb-md-0">
                        <label for="url_domain" class="form-label fw-medium">URL du Domaine <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text bg-white text-muted"><i class="bi bi-link-45deg"></i></span>
                            <input type="text" id="url_domain" name="url_domain" class="form-control" placeholder="ex: www.mon-site.com" required>
                        </div>
                        <div class="form-text small">N'incluez pas `http://` ou `https://`.</div>
                    </div>

                    <div class="col-md-3 mb-3 mb-md-0">
                        <label class="form-label fw-medium text-center d-block">Domaine par défaut</label>
                        <div class="form-check form-switch fs-5 mt-1 d-flex justify-content-center">
                            <input class="form-check-input" type="checkbox" role="switch" id="default_domain" name="default_domain" value="1">
                        </div>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label fw-medium text-center d-block">URL Canonique</label>
                        <div class="form-check form-switch fs-5 mt-1 d-flex justify-content-center">
                            <input class="form-check-input" type="checkbox" role="switch" id="canonical_domain" name="canonical_domain" value="1">
                        </div>
                    </div>
                </div>

                <hr class="my-4">
                <div class="d-flex justify-content-end">
                    <button type="submit" name="action" value="add" class="btn btn-success px-5">
                        <i class="bi bi-plus-circle me-2"></i> Créer le domaine
                    </button>
                </div>
            </form>

        </div>
    </div>
{/block}