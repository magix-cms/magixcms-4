{extends file="layout.tpl"}
{block name='head:title'}Édition d'une redirection{/block}

{block name='article'}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="bi bi-pencil-square me-2"></i>
            {if isset($redirect_data.id_redirect)}Modifier la redirection{else}Ajouter une redirection{/if}
        </h1>
        <a href="index.php?controller=SeoRedirect" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left"></i> Retour à la liste
        </a>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body p-4">

            <form action="index.php?controller=SeoRedirect&action=edit" method="post" class="validate_form edit_form">
                <input type="hidden" name="hashtoken" value="{$hashtoken}">

                {if isset($redirect_data.id_redirect)}
                    <input type="hidden" name="id_redirect" value="{$redirect_data.id_redirect}">
                {/if}

                <div class="row mb-4 bg-light p-3 rounded border">
                    <div class="col-md-5 mb-3">
                        <label for="old_url" class="form-label fw-medium">Ancienne URL <span class="text-danger">*</span></label>
                        <input type="text" name="old_url" id="old_url" class="form-control" placeholder="/ancienne-page/" value="{$redirect_data.old_url|default:''}" required>
                        <div class="form-text">Ex: /mes-anciennes-promos/</div>
                    </div>

                    <div class="col-md-5 mb-3">
                        <label for="new_url" class="form-label fw-medium">Nouvelle URL <span class="text-danger">*</span></label>
                        <input type="text" name="new_url" id="new_url" class="form-control" placeholder="/nouvelle-page/ ou https://..." value="{$redirect_data.new_url|default:''}" required>
                        <div class="form-text">L'URL de destination.</div>
                    </div>

                    <div class="col-md-2 mb-3">
                        <label for="type_redirect" class="form-label fw-medium">Type</label>
                        <select name="type_redirect" id="type_redirect" class="form-select">
                            <option value="301" {if isset($redirect_data.type_redirect) && $redirect_data.type_redirect == 301}selected{/if}>301 (Définitif)</option>
                            <option value="302" {if isset($redirect_data.type_redirect) && $redirect_data.type_redirect == 302}selected{/if}>302 (Temporaire)</option>
                        </select>
                    </div>
                </div>

                <div class="text-end">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save me-1"></i> Sauvegarder
                    </button>
                </div>
            </form>
        </div>
    </div>
{/block}