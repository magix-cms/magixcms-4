{extends file="layout.tpl"}

{block name='head:title'}Ajouter About{/block}

{block name='article'}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="bi bi-file-earmark-plus me-2"></i> Ajouter une fiche About
        </h1>
        <a href="index.php?controller=About" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left"></i> Retour liste
        </a>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body p-4">

            {* Note : Ajout de la classe "add_form" pour déclencher la redirection JS *}
            <form id="add_about_form" action="index.php?controller=About&action=add" method="post" class="validate_form add_form">
                <input type="hidden" name="hashtoken" value="{$hashtoken}">

                {* 1. BLOC DE STRUCTURE : Parent et Menu (Global) *}
                <div class="row mb-4 bg-light p-3 rounded border">
                    <div class="col-md-2 mb-3 mb-md-0">
                        <label for="parent_id" class="form-label fw-medium text-muted small">ID Parent</label>
                        {* Pré-remplissage intelligent si on vient d'un bouton "Ajouter un enfant" *}
                        <input type="text" id="parent_id" class="form-control bg-white text-center" value="{$smarty.get.parent|default:0}" readonly disabled />
                    </div>

                    <div class="col-md-7 mb-3 mb-md-0">
                        <label for="parent_select" class="form-label fw-medium">Page Parente</label>
                        <select class="form-select selectpicker" data-live-search="true" id="parent_select" name="id_parent" onchange="document.getElementById('parent_id').value = this.value;">
                            <option value="0">-- Racine (Aucun parent) --</option>
                            {if isset($aboutSelect)}
                                {foreach $aboutSelect as $item}
                                    {* Utilisation de id_about au lieu de id_pages *}
                                    <option value="{$item.id_about}" {if ($smarty.get.parent|default:0) == $item.id_about}selected{/if}>
                                        {$item.name_about|default:'Page sans nom'} (ID: {$item.id_about})
                                    </option>
                                {/foreach}
                            {/if}
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label fw-medium">Menu</label>
                        <div class="form-check form-switch fs-5 mt-1">
                            {* Par défaut, une nouvelle page est visible dans le menu *}
                            <input class="form-check-input"
                                   type="checkbox"
                                   role="switch"
                                   id="menu_about"
                                   name="menu_about"
                                   value="1" checked="checked" />
                            <label class="form-check-label fs-6 text-muted" for="menu_about">Visible</label>
                        </div>
                    </div>
                </div>

                {* 2. HEADER LANGUES *}
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="mb-0 fw-bold text-primary">Contenus</h5>
                    {if isset($langs)}
                        {include file="components/dropdown-lang.tpl"}
                    {/if}
                </div>

                {* 3. CHAMPS MULTI-LANGUES *}
                <div class="tab-content">
                    {if isset($langs)}
                        {foreach $langs as $id => $iso}
                            <fieldset class="tab-pane {if $iso@first}show active{/if}" id="lang-{$id}">

                                {* Titre & Statut *}
                                <div class="row mb-3">
                                    <div class="col-md-9">
                                        {* RETRAIT DU REQUIRED ICI COMME DEMANDÉ *}
                                        <label for="name_about_{$id}" class="form-label fw-medium">Titre</label>
                                        <input type="text" class="form-control" id="name_about_{$id}" name="content[{$id}][name_about]" value="" />
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label fw-medium">Statut</label>
                                        <div class="form-check form-switch fs-5 mt-1">
                                            <input type="hidden" name="content[{$id}][published_about]" value="0">
                                            {* Par défaut, une nouvelle traduction est publiée *}
                                            <input class="form-check-input"
                                                   type="checkbox"
                                                   role="switch"
                                                   id="switch_pub_{$id}"
                                                   name="content[{$id}][published_about]"
                                                   value="1" checked="checked" />
                                            <label class="form-check-label fs-6 text-muted" for="switch_pub_{$id}">Publiée</label>
                                        </div>
                                    </div>
                                </div>

                                {* Nom Long *}
                                <div class="row mb-3">
                                    <div class="col-md-9">
                                        <label for="longname_about_{$id}" class="form-label fw-medium">Nom long (Menu)</label>
                                        <div class="input-group">
                                            <input type="text" class="form-control" id="longname_about_{$id}" name="content[{$id}][longname_about]" value="" maxlength="125" />
                                            <span class="input-group-text bg-light text-info" data-bs-toggle="tooltip" data-bs-placement="top" title="Nom affiché dans les menus longs" style="cursor: help;">
                                                <i class="bi bi-question-circle"></i>
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                {* URL Rewriting (Sans l'URL Publique) *}
                                <div class="row mb-3">
                                    <div class="col-md-9">
                                        <label for="url_about_{$id}" class="form-label fw-medium">URL Rewriting</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light text-muted"><i class="bi bi-link-45deg"></i></span>

                                            {* Champ verrouillé et vide par défaut. Le serveur le calculera depuis le titre *}
                                            <input type="text" class="form-control bg-light" id="url_about_{$id}" name="content[{$id}][url_about]" value="" readonly placeholder="Généré automatiquement à partir du titre..." />

                                            <button class="btn btn-outline-secondary toggle-url-lock" type="button" data-target="url_about_{$id}" title="Déverrouiller pour personnaliser">
                                                <i class="bi bi-lock"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                {* Résumé *}
                                <div class="mb-3">
                                    <label for="resume_about_{$id}" class="form-label fw-medium">Résumé :</label>
                                    <textarea class="form-control" id="resume_about_{$id}" name="content[{$id}][resume_about]" rows="3"></textarea>
                                </div>

                                {* Contenu TinyMCE *}
                                <div class="mb-4">
                                    <label for="content_about_{$id}" class="form-label fw-medium">Contenu :</label>
                                    <textarea class="form-control mceEditor" id="content_about_{$id}" name="content[{$id}][content_about]" rows="10"></textarea>
                                </div>

                                {* Accordéons pour SEO et Liens *}
                                <div class="accordion mb-3" id="advancedAccordion_{$id}">

                                    {* Liens Personnalisés *}
                                    <div class="accordion-item border-0 bg-light rounded mb-2">
                                        <h2 class="accordion-header">
                                            <button class="accordion-button collapsed bg-transparent shadow-none fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#link_{$id}">
                                                <i class="bi bi-link me-2 text-primary"></i> Liens personnalisés
                                            </button>
                                        </h2>
                                        <div id="link_{$id}" class="accordion-collapse collapse" data-bs-parent="#advancedAccordion_{$id}">
                                            <div class="accordion-body bg-white border-top">
                                                <div class="mb-3">
                                                    <label for="link_label_about_{$id}" class="form-label">Label du lien :</label>
                                                    <input type="text" class="form-control" id="link_label_about_{$id}" name="content[{$id}][link_label_about]" value="">
                                                </div>
                                                <div class="mb-2">
                                                    <label for="link_title_about_{$id}" class="form-label">Titre du lien (Title) :</label>
                                                    <input type="text" class="form-control" id="link_title_about_{$id}" name="content[{$id}][link_title_about]" value="">
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    {* SEO *}
                                    <div class="accordion-item border-0 bg-light rounded">
                                        <h2 class="accordion-header">
                                            <button class="accordion-button collapsed bg-transparent shadow-none fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#seo_{$id}">
                                                <i class="bi bi-google me-2 text-primary"></i> Optimisation SEO
                                            </button>
                                        </h2>
                                        <div id="seo_{$id}" class="accordion-collapse collapse" data-bs-parent="#advancedAccordion_{$id}">
                                            <div class="accordion-body bg-white border-top">
                                                <div class="mb-3">
                                                    <label for="seo_title_about_{$id}" class="form-label d-flex justify-content-between">
                                                        Méta Titre
                                                        <span id="count-title-{$id}" class="badge bg-success">0 / 70</span>
                                                    </label>
                                                    <input type="text" class="form-control seo-counter" id="seo_title_about_{$id}" name="content[{$id}][seo_title_about]" data-target="#count-title-{$id}" data-max="70" value="">
                                                </div>
                                                <div class="mb-2">
                                                    <label for="seo_desc_about_{$id}" class="form-label d-flex justify-content-between">
                                                        Méta Description
                                                        <span id="count-desc-{$id}" class="badge bg-success">0 / 180</span>
                                                    </label>
                                                    <textarea class="form-control seo-counter" id="seo_desc_about_{$id}" name="content[{$id}][seo_desc_about]" data-target="#count-desc-{$id}" data-max="180" rows="3"></textarea>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            </fieldset>
                        {/foreach}
                    {else}
                        <div class="alert alert-warning">Aucune langue configurée.</div>
                    {/if}
                </div>

                <hr class="my-4">
                <div class="d-flex justify-content-end">
                    <button type="submit" name="action" value="add" class="btn btn-success px-5">
                        <i class="bi bi-plus-circle me-2"></i> Ajouter
                    </button>
                </div>
            </form>

        </div>
    </div>
{/block}

{block name="javascripts" append}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Script de verrouillage/déverrouillage de l'URL (Identique à Pages)
            document.addEventListener('click', function(e) {
                const btn = e.target.closest('.toggle-url-lock');

                if (btn) {
                    e.preventDefault();
                    const targetId = btn.getAttribute('data-target');
                    const input = document.getElementById(targetId);
                    const icon = btn.querySelector('i');

                    if (input) {
                        if (input.hasAttribute('readonly')) {
                            input.removeAttribute('readonly');
                            input.classList.remove('bg-light');
                            icon.classList.remove('bi-lock');
                            icon.classList.add('bi-unlock', 'text-warning');
                            btn.setAttribute('title', 'Verrouiller l\'URL');
                            input.focus();
                        } else {
                            input.setAttribute('readonly', 'readonly');
                            input.classList.add('bg-light');
                            icon.classList.remove('bi-unlock', 'text-warning');
                            icon.classList.add('bi-lock');
                            btn.setAttribute('title', 'Déverrouiller pour personnaliser');
                        }
                    }
                }
            });

            // Script Compteurs SEO (simple inline si MagixFormTools n'est pas utilisé)
            const counters = document.querySelectorAll('.seo-counter');
            counters.forEach(input => {
                input.addEventListener('input', function() {
                    const max = this.getAttribute('data-max');
                    const target = document.querySelector(this.getAttribute('data-target'));
                    const current = this.value.length;
                    target.textContent = current + ' / ' + max;
                    if(current > max) { target.classList.replace('bg-success', 'bg-danger'); }
                    else { target.classList.replace('bg-danger', 'bg-success'); }
                });
            });
        });
    </script>
{/block}