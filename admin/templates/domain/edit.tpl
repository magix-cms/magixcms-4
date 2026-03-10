{extends file="layout.tpl"}

{block name='head:title'}{#edit_domain#|ucfirst}{/block}
{block name='body:id'}domain{/block}

{block name='article'}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="bi bi-pencil-square me-2"></i> {#edit_domain#|ucfirst} : <span class="text-primary">{$domain.url_domain}</span>
        </h1>
        <a href="index.php?controller=Domain" class="btn btn-outline-secondary btn-sm shadow-sm">
            <i class="bi bi-arrow-left"></i> Retour à la liste
        </a>
    </div>

    <div class="card shadow-sm border-0">
        <header class="card-header bg-white p-0 border-bottom-0">
            <ul class="nav nav-tabs nav-fill" role="tablist" id="domainEditTab">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active py-3 fw-bold" id="general-tab" data-bs-toggle="tab" data-bs-target="#general" type="button" role="tab" aria-controls="general" aria-selected="true">
                        <i class="bi bi-globe me-2"></i> {#domain#|ucfirst}
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link py-3 fw-bold" id="sitemap-tab" data-bs-toggle="tab" data-bs-target="#sitemap" type="button" role="tab" aria-controls="sitemap" aria-selected="false">
                        <i class="bi bi-diagram-3 me-2"></i> Sitemap
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link py-3 fw-bold" id="langs-tab" data-bs-toggle="tab" data-bs-target="#langs" type="button" role="tab" aria-controls="langs" aria-selected="false">
                        <i class="bi bi-translate me-2"></i> Langues
                    </button>
                </li>
            </ul>
        </header>

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

                        {* Section Tracking *}
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
                                            <textarea id="tracking_domain" name="tracking_domain" class="form-control font-monospace text-muted" rows="6">{$domain.tracking_domain}</textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <hr class="my-4">
                        <div class="d-flex justify-content-end">
                            <button class="btn btn-primary px-5 shadow-sm" type="submit" name="action" value="save">
                                <i class="bi bi-save me-2"></i> {#save#|ucfirst}
                            </button>
                        </div>
                    </form>
                </div>

                {* ==========================================================
                   ONGLET 2 : SITEMAP
                   ========================================================== *}
                <div role="tabpanel" class="tab-pane fade" id="sitemap" aria-labelledby="sitemap-tab">

                    <div class="d-flex justify-content-between align-items-center mb-4 pb-3 border-bottom">
                        <div>
                            <h5 class="mb-1 fw-bold text-primary">Génération XML</h5>
                            <p class="text-muted small mb-0">Les sitemaps aident les moteurs de recherche à explorer votre site.</p>
                        </div>
                        <button type="button" class="btn btn-warning shadow-sm fw-bold" id="btnGenerateSitemap" data-id="{$domain.id_domain}">
                            <i class="bi bi-arrow-repeat me-2"></i> Générer / Mettre à jour
                        </button>
                    </div>

                    <div class="row mb-3">
                        <div class="col-12">
                            <h6 class="fw-bold mb-3"><i class="bi bi-link-45deg me-1"></i> Liens attendus (Index & Langues)</h6>
                            <ul class="list-group list-group-flush border rounded shadow-sm mb-4">
                                {* Index Mère (Utilisation des variables PHP propres) *}
                                <li class="list-group-item d-flex justify-content-between align-items-center p-3 bg-light">
                                    <a href="{$base_url}/sitemap-{$clean_domain}.xml" target="_blank" class="text-decoration-none fw-bold text-dark">
                                        <i class="bi bi-diagram-3-fill text-primary me-2"></i> {$base_url}/sitemap-{$clean_domain}.xml <span class="badge bg-primary ms-2">Index Mère</span>
                                    </a>
                                </li>

                                {* Boucle sur les langues effectives du domaine (Sitemap Langs) *}
                                {if isset($sitemap_langs) && !empty($sitemap_langs)}
                                    {foreach $sitemap_langs as $dLang}
                                        {$iso = $dLang.iso_lang|lower}
                                        <li class="list-group-item d-flex justify-content-between align-items-center p-3 ps-5">
                                            <a href="{$base_url}/{$iso}-sitemap-{$clean_domain}.xml" target="_blank" class="text-decoration-none fw-medium text-secondary">
                                                <i class="bi bi-filetype-xml me-2"></i> {$base_url}/<span class="text-dark fw-bold">{$iso}</span>-sitemap-{$clean_domain}.xml
                                            </a>
                                            <span class="badge bg-secondary">Pages & Produits</span>
                                        </li>
                                        <li class="list-group-item d-flex justify-content-between align-items-center p-3 ps-5">
                                            <a href="{$base_url}/{$iso}-sitemap-image-{$clean_domain}.xml" target="_blank" class="text-decoration-none fw-medium text-secondary">
                                                <i class="bi bi-image me-2"></i> {$base_url}/<span class="text-dark fw-bold">{$iso}</span>-sitemap-image-{$clean_domain}.xml
                                            </a>
                                            <span class="badge bg-info text-dark">Images</span>
                                        </li>
                                    {/foreach}
                                {/if}
                            </ul>

                            <div class="alert alert-info border-0 shadow-sm small">
                                <i class="bi bi-info-circle-fill fs-5 me-2 float-start"></i>
                                <strong>Astuce SEO :</strong> C'est uniquement l'URL de <strong>l'Index Mère</strong> que vous devez soumettre dans votre <em>Google Search Console</em>. Google trouvera automatiquement les sous-sitemaps de langues et d'images.
                            </div>
                        </div>
                    </div>
                </div>

                {* ==========================================================
                   ONGLET 3 : LANGUES
                   ========================================================== *}
                <div role="tabpanel" class="tab-pane fade" id="langs" aria-labelledby="langs-tab">
                    <form id="edit_domain_langs_form" action="index.php?controller=Domain&action=saveDomainLanguages" method="post" class="validate_form edit_form">
                        <input type="hidden" name="hashtoken" value="{$hashtoken}">
                        <input type="hidden" name="id_domain" value="{$domain.id_domain}">

                        <div class="row mb-4 bg-light p-3 rounded border">
                            <div class="col-12">
                                <p class="text-muted mb-3">Sélectionnez les langues disponibles pour ce domaine et choisissez la langue principale.</p>

                                <div class="table-responsive">
                                    <table class="table table-hover align-middle bg-white border rounded shadow-sm mb-0">
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
                            <button class="btn btn-primary px-5 shadow-sm" type="submit" name="action" value="save">
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
        document.addEventListener('DOMContentLoaded', () => {

            // --- 1. UI Dynamique pour les langues ---
            document.addEventListener('change', function(e) {
                if (e.target.classList.contains('lang-enable-cb')) {
                    const tr = e.target.closest('tr');
                    const radio = tr.querySelector('.lang-default-radio');

                    if (e.target.checked) {
                        radio.disabled = false;
                        if (document.querySelectorAll('.lang-default-radio:checked').length === 0) {
                            radio.checked = true;
                        }
                    } else {
                        radio.disabled = true;
                        if (radio.checked) {
                            radio.checked = false;
                            const firstCheckedCb = document.querySelector('.lang-enable-cb:checked');
                            if (firstCheckedCb) {
                                firstCheckedCb.closest('tr').querySelector('.lang-default-radio').checked = true;
                            }
                        }
                    }
                }
            });

            // --- 2. Requête AJAX Sitemap ---
            const btnSitemap = document.getElementById('btnGenerateSitemap');
            if (btnSitemap) {
                btnSitemap.addEventListener('click', async () => {
                    const idDomain = btnSitemap.dataset.id;
                    const token = document.querySelector('input[name="hashtoken"]').value;

                    const originalHtml = btnSitemap.innerHTML;
                    btnSitemap.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Génération...';
                    btnSitemap.disabled = true;

                    const formData = new URLSearchParams();
                    formData.append('id_domain', idDomain);
                    formData.append('hashtoken', token);

                    try {
                        const response = await fetch('index.php?controller=Domain&action=generateDomainSitemap', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded',
                                'X-Requested-With': 'XMLHttpRequest'
                            },
                            body: formData.toString()
                        });

                        const data = await response.json();

                        if (data.status) {
                            if (typeof MagixToast !== 'undefined') MagixToast.success(data.message);
                        } else {
                            if (typeof MagixToast !== 'undefined') MagixToast.error(data.message);
                        }
                    } catch (error) {
                        console.error("Erreur SITEMAP:", error);
                        if (typeof MagixToast !== 'undefined') MagixToast.error("Erreur de connexion.");
                    } finally {
                        btnSitemap.innerHTML = originalHtml;
                        btnSitemap.disabled = false;
                    }
                });
            }
        });
    </script>
{/block}