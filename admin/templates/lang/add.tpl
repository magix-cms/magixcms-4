{extends file="layout.tpl"}
{block name='head:title'}{#add_language#}{/block}
{block name='body:id'}language{/block}

{block name='article'}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="bi bi-translate me-2"></i> {#add_language#}
        </h1>
        <a href="index.php?controller=Lang" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left"></i> {#back_to_list#}
        </a>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body p-4">

            <form action="index.php?controller=Lang&action=add" method="post" class="validate_form add_form">
                <input type="hidden" name="hashtoken" value="{$hashtoken}">

                <div class="row mb-4 bg-light p-3 rounded border">

                    {* --- CHAMP SELECT --- *}
                    <div class="col-md-5 mb-3 mb-md-0">
                        <label for="name_lang" class="form-label fw-medium">{#language#} <span class="text-danger">*</span></label>
                        <select name="name_lang" id="name_lang" class="form-select" required>
                            <option value="">Sélectionnez une langue...</option>
                            {if isset($available_languages)}
                                {foreach $available_languages as $iso => $name}
                                    <option value="{$name}" data-iso="{$iso}">{$name}</option>
                                {/foreach}
                            {/if}
                        </select>
                    </div>

                    {* --- CHAMP ISO --- *}
                    <div class="col-md-3 mb-3 mb-md-0">
                        <label for="iso_lang" class="form-label fw-medium">{#iso_lang#} <span class="text-danger">*</span></label>
                        <input type="text" id="iso_lang" name="iso_lang" class="form-control text-center font-monospace text-uppercase" placeholder="ex: FR" maxlength="5" required>
                    </div>

                    <div class="col-md-2 mb-3 mb-md-0 text-center">
                        <label class="form-label fw-medium d-block">{#active#}</label>
                        <div class="form-check form-switch fs-5 mt-1 d-inline-block">
                            <input class="form-check-input" type="checkbox" role="switch" name="active_lang" value="1" checked>
                        </div>
                    </div>

                    <div class="col-md-2 text-center">
                        <label class="form-label fw-medium d-block">{#default_lang#}</label>
                        <div class="form-check form-switch fs-5 mt-1 d-inline-block">
                            <input class="form-check-input" type="checkbox" role="switch" name="default_lang" value="1">
                        </div>
                    </div>
                </div>

                <hr class="my-4">
                <div class="d-flex justify-content-end">
                    <button type="submit" name="action" value="add" class="btn btn-success px-5">
                        <i class="bi bi-plus-circle me-2"></i> {#add_language#}
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
                // 1. Sélection -> Met à jour l'input ISO
                selectLang.addEventListener('change', function() {
                    const selectedOption = this.options[this.selectedIndex];
                    if (selectedOption && selectedOption.value !== "") {
                        const iso = selectedOption.getAttribute('data-iso');
                        if(iso) inputIso.value = iso.toUpperCase();
                    } else {
                        inputIso.value = "";
                    }
                });

                // 2. Frappe dans l'input ISO -> Met à jour le menu déroulant
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