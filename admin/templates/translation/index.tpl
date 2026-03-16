{extends file="layout.tpl"}

{block name='head:title'}Gestion des traductions{/block}

{block name='article'}
    {* 1. EN-TÊTE DE LA PAGE *}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="bi bi-translate me-2"></i> Traductions
        </h1>

        {* Sélecteur de domaine *}
        <form method="GET" action="index.php" class="d-flex align-items-center">
            <input type="hidden" name="controller" value="Translation">
            <label for="domainSelect" class="me-2 small fw-bold text-muted text-nowrap">Contexte :</label>
            <select name="domain" id="domainSelect" class="form-select form-select-sm border-primary-subtle shadow-sm" onchange="this.form.submit()">
                <option value="theme" {if $domain == 'theme'}selected{/if}>Thème Actif</option>
                {if isset($plugins) && $plugins|count > 0}
                    <optgroup label="Plugins">
                        {foreach $plugins as $plugin}
                            <option value="{$plugin.name}" {if $domain == $plugin.name}selected{/if}>Plugin : {$plugin.name}</option>
                        {/foreach}
                    </optgroup>
                {/if}
            </select>
        </form>
    </div>

    <div class="alert alert-info border-0 shadow-sm mb-4">
        <i class="bi bi-info-circle me-2"></i> Vous éditez actuellement les traductions pour : <strong>{$domain_label}</strong>.
        Dans le frontend, utilisez <code>{ldelim}#ma_cle#{rdelim}</code> pour les afficher.
    </div>

    <div class="card shadow-sm border-0">
        {* 2. HEADER DE LA CARTE AVEC LE DROPDOWN DE LANGUE *}
        <div class="card-header bg-white py-3 d-flex align-items-center justify-content-between border-bottom-0">
            <h6 class="m-0 fw-bold text-primary">Fichiers .conf ({$domain})</h6>

            {if isset($langs)}
                {include file="components/dropdown-lang.tpl"}
            {/if}
        </div>

        <div class="card-body bg-light">
            {* 3. LE FORMULAIRE UNIQUE POUR TOUTES LES LANGUES *}
            {* On a retiré 'validate_form' pour laisser notre classe JS faire le travail toute seule *}
            <form id="edit_translations" action="index.php?controller=Translation&action=save" method="post">
                <input type="hidden" name="hashtoken" value="{$hashtoken|default:''}">
                <input type="hidden" name="domain" value="{$domain}">

                <div class="tab-content">
                    {if isset($langs)}
                        {foreach $langs as $id => $iso}
                            {* L'id "lang-{$id}" est crucial pour faire le lien avec dropdown-lang.tpl *}
                            <fieldset role="tabpanel" class="tab-pane {if $iso@first}show active{/if}" id="lang-{$id}">

                                <div class="row g-4"> {* Remplacement de g-3 par g-4 pour un peu plus d'espace vertical *}
                                    {* Variables existantes *}
                                    {if $keys|count > 0}
                                        {foreach $keys as $key}
                                            {* 🟢 MODIFICATION ICI : col-12 pour prendre toute la largeur *}
                                            <div class="col-12">
                                                <div class="form-group bg-white p-3 rounded border shadow-sm">
                                                    <label class="form-label fw-bold text-primary mb-2">{$key}</label>
                                                    {* 🟢 MODIFICATION ICI : rows="3" et retrait de form-control-sm *}
                                                    <textarea class="form-control"
                                                              name="content[{$iso}][{$key}]"
                                                              rows="1">{$translations.$iso.$key|default:''|escape}</textarea>
                                                </div>
                                            </div>
                                        {/foreach}
                                    {else}
                                        <div class="col-12 text-center text-muted py-4">
                                            <i class="bi bi-journal-x fs-1 d-block mb-2"></i>
                                            Aucune variable définie pour le moment.
                                        </div>
                                    {/if}
                                </div>

                            </fieldset>
                        {/foreach}

                        {* 4. BLOC GLOBAL POUR AJOUTER UNE NOUVELLE VARIABLE *}
                        <div class="mt-5 p-4 bg-primary bg-opacity-10 border border-primary-subtle rounded shadow-sm">
                            <h5 class="fw-bold text-primary mb-3"><i class="bi bi-plus-circle me-2"></i>Créer une nouvelle variable</h5>
                            <div class="row g-4">
                                {* 🟢 MODIFICATION : On passe la clé en haut (pleine largeur) ou on garde une belle disposition *}
                                <div class="col-12">
                                    <label class="form-label fw-bold">Clé (Ex: texte_bienvenue)</label>
                                    <input type="text" name="new_key" class="form-control" placeholder="Clé unique (sans espaces)">
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-bold">Valeurs traduites</label>
                                    <div class="row g-3">
                                        {foreach $langs as $id => $iso}
                                            <div class="col-12 d-flex align-items-start">
                                                <span class="badge bg-secondary me-3 mt-1" style="width:45px; padding: 0.5em;">{$iso|upper}</span>
                                                {* 🟢 MODIFICATION : Transformation en textarea pour la création aussi *}
                                                <textarea name="new_value[{$iso}]" class="form-control" rows="1" placeholder="Traduction en {$iso|upper}"></textarea>
                                            </div>
                                        {/foreach}
                                    </div>
                                </div>
                            </div>
                        </div>

                    {else}
                        <div class="alert alert-warning">Aucune langue configurée ou transmise à la vue.</div>
                    {/if}
                </div>

                <hr class="my-4">
                <div class="d-flex justify-content-end">
                    <button class="btn btn-primary px-4" type="submit">
                        <i class="bi bi-save me-1"></i> Tout enregistrer
                    </button>
                </div>
            </form>
        </div>
    </div>
{/block}

{block name='javascripts' append}
    {* Adaptez le chemin selon l'endroit où vous stockez vos JS backend *}
    <script src="{$site_url}/{$baseadmin}/templates/js/MagixTranslation.min.js?v={$smarty.now}"></script>
{/block}