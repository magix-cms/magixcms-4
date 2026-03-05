{extends file="layout.tpl"}
{block name='head:title'}Éditer un tag{/block}

{block name='article'}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="bi bi-pencil-square me-2"></i> Éditer le tag : <span class="text-primary">{$tag_data.name_tag}</span>
        </h1>
        <a href="index.php?controller=NewsTag" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left"></i> Retour à la liste
        </a>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body p-4">
            <form action="index.php?controller=NewsTag&action=edit" method="post" class="validate_form edit_form">
                <input type="hidden" name="hashtoken" value="{$hashtoken}">
                <input type="hidden" name="id_tag" value="{$tag_data.id_tag}">

                <div class="row mb-4 bg-light p-3 rounded border">
                    <div class="col-md-8 mb-3 mb-md-0">
                        <label for="name_tag" class="form-label fw-medium">Nom du mot-clé <span class="text-danger">*</span></label>
                        <input type="text" id="name_tag" name="name_tag" class="form-control" value="{$tag_data.name_tag}" required>
                    </div>

                    <div class="col-md-4">
                        <label for="id_lang" class="form-label fw-medium">Langue associée <span class="text-danger">*</span></label>
                        <select name="id_lang" id="id_lang" class="form-select" required>
                            <option value="">-- Choisir --</option>
                            {if isset($langs)}
                                {foreach $langs as $id => $iso}
                                    <option value="{$id}" {if $tag_data.id_lang == $id}selected{/if}>{$iso|upper}</option>
                                {/foreach}
                            {/if}
                        </select>
                    </div>
                </div>

                <hr class="my-4">
                <div class="d-flex justify-content-end">
                    <button type="submit" name="action" value="save" class="btn btn-primary px-5">
                        <i class="bi bi-save me-2"></i> {#save#}
                    </button>
                </div>
            </form>
        </div>
    </div>
{/block}