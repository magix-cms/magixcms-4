{extends file="layout.tpl"}
{block name='head:title'}{#edit_language#}{/block}
{block name='body:id'}language{/block}

{block name='article'}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="bi bi-pencil-square me-2"></i> {#edit_language#} : <span class="text-primary">{$lang_data.name_lang}</span>
        </h1>
        <a href="index.php?controller=Lang" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left"></i> {#back_to_list#}
        </a>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body p-4">

            <form action="index.php?controller=Lang&action=edit" method="post" class="validate_form edit_form">
                <input type="hidden" name="hashtoken" value="{$hashtoken}">
                <input type="hidden" name="id_lang" value="{$lang_data.id_lang}">

                <div class="row mb-4 bg-light p-3 rounded border">

                    <div class="col-md-5 mb-3 mb-md-0">
                        <label for="name_lang" class="form-label fw-medium">{#language#} <span class="text-danger">*</span></label>
                        <select name="name_lang" id="name_lang" class="form-select" required>
                            <option value="">Sélectionnez une langue...</option>
                            {if isset($available_languages)}
                                {foreach $available_languages as $iso => $name}
                                    <option value="{$name}" data-iso="{$iso}" {if $lang_data.iso_lang|lower == $iso|lower}selected{/if}>{$name}</option>
                                {/foreach}
                            {/if}
                        </select>
                    </div>

                    <div class="col-md-3 mb-3 mb-md-0">
                        <label for="iso_lang" class="form-label fw-medium">{#iso_lang#} <span class="text-danger">*</span></label>
                        <input type="text" id="iso_lang" name="iso_lang" class="form-control text-center font-monospace text-uppercase" value="{$lang_data.iso_lang|upper}" maxlength="5" required>
                    </div>

                    <div class="col-md-2 mb-3 mb-md-0 text-center">
                        <label class="form-label fw-medium d-block">{#active#}</label>
                        <div class="form-check form-switch fs-5 mt-1 d-inline-block">
                            <input class="form-check-input" type="checkbox" role="switch" name="active_lang" value="1" {if $lang_data.active_lang == 1}checked{/if}>
                        </div>
                    </div>

                    <div class="col-md-2 text-center">
                        <label class="form-label fw-medium d-block">{#default_lang#}</label>
                        <div class="form-check form-switch fs-5 mt-1 d-inline-block">
                            <input class="form-check-input" type="checkbox" role="switch" name="default_lang" value="1" {if $lang_data.default_lang == 1}checked{/if}>
                        </div>
                        {if $lang_data.default_lang == 1}
                            <div class="form-text small text-success mt-1">Langue principale du site</div>
                        {/if}
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

{block name="javascripts" append}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const selectLang = document.getElementById('name_lang');
            const inputIso = document.getElementById('iso_lang');

            if (selectLang && inputIso) {
                selectLang.addEventListener('change', function() {
                    const selectedOption = this.options[this.selectedIndex];
                    if (selectedOption && selectedOption.value !== "") {
                        const iso = selectedOption.getAttribute('data-iso');
                        if(iso) inputIso.value = iso.toUpperCase();
                    } else {
                        inputIso.value = "";
                    }
                });

                inputIso.addEventListener('input', function() {
                    const currentIso = this.value.toLowerCase().trim();
                    let found = false;

                    for (let i = 0; i < selectLang.options.length; i++) {
                        const optIso = selectLang.options[i].getAttribute('data-iso');
                        if (optIso && optIso.toLowerCase() === currentIso) {
                            selectLang.selectedIndex = i;
                            found = true;
                            break;
                        }
                    }

                    if (!found && currentIso === "") {
                        selectLang.selectedIndex = 0;
                    }
                });
            }
        });
    </script>
{/block}