{extends file="layout.tpl"}

{block name='head:title'}{#edit_domain#|ucfirst}{/block}

{block name='article'}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="bi bi-pencil-square me-2"></i> {#edit_domain#|ucfirst} : <span class="text-primary">{$domain.url_domain}</span>
        </h1>
        <a href="index.php?controller=Domain" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left"></i> Retour à la liste
        </a>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-header bg-white py-3 border-bottom-0">
            <ul class="nav nav-tabs card-header-tabs m-0" role="tablist" id="domainEditTab">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active fw-bold text-dark" id="general-tab" data-bs-toggle="tab" data-bs-target="#general" type="button" role="tab" aria-controls="general" aria-selected="true">
                        <i class="bi bi-globe me-2"></i> {#domain#|ucfirst}
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link fw-bold text-dark" id="sitemap-tab" data-bs-toggle="tab" data-bs-target="#sitemap" type="button" role="tab" aria-controls="sitemap" aria-selected="false">
                        <i class="bi bi-diagram-3 me-2"></i> Sitemap
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link fw-bold text-dark" id="langs-tab" data-bs-toggle="tab" data-bs-target="#langs" type="button" role="tab" aria-controls="langs" aria-selected="false">
                        <i class="bi bi-translate me-2"></i> Langues
                    </button>
                </li>
            </ul>
        </div>

        <div class="card-body p-4">
            <div class="tab-content">

                {* ==========================================================
                   ONGLET 1 : GÉNÉRAL
                   ========================================================== *}
                <div role="tabpanel" class="tab-pane fade show active" id="general" aria-labelledby="general-tab">

                    <form id="edit_domain_form" action="index.php?controller=Domain&action=edit" method="post" class="validate_form edit_form">
                        <input type="hidden" name="hashtoken" value="{$hashtoken}">
                        <input type="hidden" name="id_domain" value="{$domain.id_domain}">

                        <div class="row mb-4 bg-light p-3 rounded border">
                            <div class="col-md-6 mb-3 mb-md-0">
                                <label for="url_domain" class="form-label fw-medium">URL du Domaine <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white text-muted"><i class="bi bi-link-45deg"></i></span>
                                    <input type="text" id="url_domain" name="url_domain" class="form-control" value="{$domain.url_domain}" required>
                                </div>
                            </div>

                            <div class="col-md-3 mb-3 mb-md-0">
                                <label class="form-label fw-medium text-center d-block">Domaine par défaut</label>
                                <div class="form-check form-switch fs-5 mt-1 d-flex justify-content-center">
                                    <input class="form-check-input" type="checkbox" role="switch" name="default_domain" value="1" {if $domain.default_domain == 1}checked{/if}>
                                </div>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label fw-medium text-center d-block">URL Canonique</label>
                                <div class="form-check form-switch fs-5 mt-1 d-flex justify-content-center">
                                    <input class="form-check-input" type="checkbox" role="switch" name="canonical_domain" value="1" {if $domain.canonical_domain == 1}checked{/if}>
                                </div>
                            </div>
                        </div>

                        {* Section Tracking (Style Accordéon comme dans Pages) *}
                        <div class="accordion mb-3" id="advancedAccordion_domain">
                            <div class="accordion-item border-0 bg-light rounded mb-2">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed bg-transparent shadow-none fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#tracking_domain_panel">
                                        <i class="bi bi-code-slash me-2 text-primary"></i> Scripts de Tracking
                                    </button>
                                </h2>
                                <div id="tracking_domain_panel" class="accordion-collapse collapse" data-bs-parent="#advancedAccordion_domain">
                                    <div class="accordion-body bg-white border-top">
                                        <div class="mb-2">
                                            <label for="tracking_domain" class="form-label text-muted small">Insérez ici vos tags Google Analytics, Pixel Facebook, etc. pour ce domaine.</label>
                                            <textarea id="tracking_domain" name="tracking_domain" class="form-control font-monospace text-muted" rows="6" placeholder="">{$domain.tracking_domain}</textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <hr class="my-4">
                        <div class="d-flex justify-content-end">
                            <button class="btn btn-primary px-5" type="submit" name="action" value="save">
                                <i class="bi bi-save me-2"></i> {#save#|ucfirst}
                            </button>
                        </div>
                    </form>

                </div>

                {* ==========================================================
                   ONGLET 2 : SITEMAP
                   ========================================================== *}
                <div role="tabpanel" class="tab-pane fade" id="sitemap" aria-labelledby="sitemap-tab">

                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="mb-0 fw-bold text-primary">Fichiers Sitemap XML</h5>
                    </div>

                    <div class="row mb-3">
                        <div class="col-12">
                            {if isset($xmlItems) && !empty($xmlItems)}
                                <ul class="list-group list-group-flush border rounded shadow-sm">
                                    {foreach $xmlItems as $xml}
                                        <li class="list-group-item d-flex justify-content-between align-items-center p-3">
                                            <a href="{$xml.url}" target="_blank" class="text-decoration-none fw-medium text-dark">
                                                <i class="bi bi-filetype-xml text-secondary me-2"></i> {$xml.url}
                                            </a>
                                            <a href="{$xml.url}" target="_blank" class="btn btn-sm btn-outline-secondary" title="Ouvrir dans un nouvel onglet">
                                                <i class="bi bi-box-arrow-up-right"></i>
                                            </a>
                                        </li>
                                    {/foreach}
                                </ul>
                            {else}
                                <div class="alert alert-light border border-dashed text-center py-4 text-muted">
                                    <i class="bi bi-diagram-3 fs-2 d-block mb-2"></i>
                                    Aucun fichier sitemap généré pour ce domaine.
                                </div>
                            {/if}
                        </div>
                    </div>
                </div>

                {* ONGLET 3 : LANGUES *}
                <div role="tabpanel" class="tab-pane fade" id="langs" aria-labelledby="langs-tab">

                    <form id="edit_domain_langs_form" action="index.php?controller=Domain&action=saveDomainLanguages" method="post" class="validate_form edit_form">
                        <input type="hidden" name="hashtoken" value="{$hashtoken}">
                        <input type="hidden" name="id_domain" value="{$domain.id_domain}">

                        <div class="row mb-4 bg-light p-3 rounded border">
                            <div class="col-12">
                                <p class="text-muted mb-3">Sélectionnez les langues disponibles pour ce domaine et choisissez la langue principale.</p>

                                <div class="table-responsive">
                                    <table class="table table-hover align-middle bg-white border rounded shadow-sm">
                                        <thead class="table-light">
                                        <tr>
                                            <th class="text-center" style="width: 80px;">Activer</th>
                                            <th>Langue</th>
                                            <th class="text-center" style="width: 150px;">Par défaut</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        {if isset($all_langs) && !empty($all_langs)}
                                            {foreach $all_langs as $lang}
                                                {* Vérification si la langue est actuellement liée *}
                                                {$isActive = isset($domain_langs[$lang.id_lang])}
                                                {$isDefault = $isActive && $domain_langs[$lang.id_lang].default_lang == 1}

                                                <tr>
                                                    <td class="text-center">
                                                        <div class="form-check d-flex justify-content-center m-0">
                                                            <input class="form-check-input lang-enable-cb fs-5" type="checkbox" name="langs[]" value="{$lang.id_lang}" id="lang_{$lang.id_lang}" {if $isActive}checked{/if}>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <label class="form-check-label d-block cursor-pointer py-2" for="lang_{$lang.id_lang}">
                                                            <span class="flag-icon flag-icon-{$lang.iso_lang|lower} me-2 border"></span>
                                                            <span class="fw-medium">{$lang.name_lang|default:$lang.iso_lang|upper}</span>
                                                        </label>
                                                    </td>
                                                    <td class="text-center">
                                                        <div class="form-check d-flex justify-content-center m-0">
                                                            {* Ce radio button est désactivé si la case 'Activer' n'est pas cochée *}
                                                            <input class="form-check-input lang-default-radio fs-5" type="radio" name="default_lang" value="{$lang.id_lang}" {if $isDefault}checked{/if} {if !$isActive}disabled{/if}>
                                                        </div>
                                                    </td>
                                                </tr>
                                            {/foreach}
                                        {else}
                                            <tr>
                                                <td colspan="3" class="text-center text-muted py-4">Aucune langue configurée dans le système.</td>
                                            </tr>
                                        {/if}
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <hr class="my-4">
                        <div class="d-flex justify-content-end">
                            <button class="btn btn-primary px-5" type="submit" name="action" value="save">
                                <i class="bi bi-save me-2"></i> {#save#}
                            </button>
                        </div>
                    </form>
                </div>

            </div>
        </div>
    </div>
{/block}

{block name="javascripts" append}
<script>
    // UI dynamique pour l'onglet Langues (Domaines)
    document.addEventListener('change', function(e) {
        if (e.target.classList.contains('lang-enable-cb')) {
            const tr = e.target.closest('tr');
            const radio = tr.querySelector('.lang-default-radio');

            if (e.target.checked) {
                // On active le radio
                radio.disabled = false;
                // S'il n'y a aucun radio coché, on coche celui-ci par défaut
                if (document.querySelectorAll('.lang-default-radio:checked').length === 0) {
                    radio.checked = true;
                }
            } else {
                // On désactive le radio
                radio.disabled = true;
                // Si on vient de décocher la langue par défaut
                if (radio.checked) {
                    radio.checked = false;
                    // On trouve la première autre langue cochée et on la met par défaut
                    const firstCheckedCb = document.querySelector('.lang-enable-cb:checked');
                    if (firstCheckedCb) {
                        firstCheckedCb.closest('tr').querySelector('.lang-default-radio').checked = true;
                    }
                }
            }
        }
    });
</script>
{/block}