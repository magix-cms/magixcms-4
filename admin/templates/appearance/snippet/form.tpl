{extends file="layout.tpl"}

{block name='head:title'}
    {if $snippet.id_snippet > 0}
        {#edit_snippet#|default:'Éditer le modèle'}
    {else}
        {#add_snippet#|default:'Ajouter un modèle'}
    {/if}
{/block}

{block name='body:id'}snippet-form{/block}

{block name='article'}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="bi bi-code-square me-2"></i>
            {if $snippet.id_snippet > 0}
                {#edit_snippet#|default:'Éditer le modèle'}
            {else}
                {#add_snippet#|default:'Ajouter un modèle'}
            {/if}
        </h1>
        <a href="index.php?controller=Snippet" class="btn btn-outline-secondary shadow-sm">
            <i class="bi bi-arrow-left me-1"></i> {#back_to_list#|default:'Retour à la liste'}
        </a>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body p-4">

            <form id="snippet_form" action="index.php?controller=Snippet&action=save" method="post" class="validate_form {if $snippet.id_snippet > 0}edit_form{else}add_form{/if}">

                <input type="hidden" name="hashtoken" value="{$hashtoken}">
                <input type="hidden" name="id_snippet" value="{$snippet.id_snippet}">

                <div class="row g-4">
                    <div class="col-md-6">
                        <label class="form-label fw-bold" for="title_sp">{#title#|default:'Titre du modèle'} <span class="text-danger">*</span></label>
                        <input type="text" id="title_sp" name="title_sp" class="form-control" value="{$snippet.title_sp|default:''}" required placeholder="Ex: Bloc alerte information" />
                        <div class="form-text text-muted">Ce titre apparaîtra dans la liste de la fenêtre TinyMCE.</div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-bold" for="description_sp">{#description#|default:'Description'}</label>
                        <input type="text" id="description_sp" name="description_sp" class="form-control" value="{$snippet.description_sp|default:''}" placeholder="Ex: Affiche une boîte d'alerte bleue" />
                        <div class="form-text text-muted">Courte description visible par les rédacteurs (Optionnel).</div>
                    </div>

                    <div class="col-12">
                        <label class="form-label fw-bold" for="content_sp">{#content#|default:'Contenu du modèle'}</label>
                        <textarea id="content_sp"
                                  name="content_sp"
                                  class="form-control mceEditor"
                                  rows="15"
                                  placeholder="Saisissez le contenu de votre modèle..."
                                  data-controller="snippet"
                                  data-itemid="{$snippet.id_snippet|default:0}"
                                  data-lang="1"
                                  data-field="content_sp">{$snippet.content_sp|default:''}</textarea>
                    </div>
                </div>

                <hr class="my-4">
                <div class="d-flex justify-content-end">
                    <button class="btn btn-primary px-5" type="submit" name="action" value="save">
                        <i class="bi bi-save me-2"></i> {if $snippet.id_snippet > 0}{#update#|default:'Mettre à jour'}{else}{#save#|default:'Enregistrer'}{/if}
                    </button>
                </div>

            </form>

        </div>
    </div>
{/block}