<form id="contact-form" class="validate_form" method="post" action="{$base_url}{$current_lang.iso_lang}/contact/send">

    {* Champ de sécurité anti-CSRF *}
    <input type="hidden" name="csrf_token" value="{$csrf_token|default:''}">

    <div class="row g-3">
        <div class="col-12 col-md-6">
            <div class="form-group mb-3">
                <label for="firstname" class="form-label fw-bold">
                    {#contact_firstname_label#} <span class="text-danger">*</span>
                </label>
                <input id="firstname" type="text" name="msg[firstname]"
                       placeholder="{#contact_firstname_placeholder#}" class="form-control" required/>
            </div>
        </div>
        <div class="col-12 col-md-6">
            <div class="form-group mb-3">
                <label for="lastname" class="form-label fw-bold">
                    {#contact_lastname_label#} <span class="text-danger">*</span>
                </label>
                <input id="lastname" type="text" name="msg[lastname]"
                       placeholder="{#contact_lastname_placeholder#}" class="form-control" required/>
            </div>
        </div>
    </div>

    <div class="form-group mb-3">
        <label for="email" class="form-label fw-bold">
            {#contact_email_label#} <span class="text-danger">*</span>
        </label>
        <input id="email" type="email" name="msg[email]"
               placeholder="{#contact_email_placeholder#}" class="form-control" required/>
    </div>

    <div class="form-group mb-3">
        <label for="phone" class="form-label fw-bold">{#contact_phone_label#}</label>
        <input id="phone" type="tel" name="msg[phone]"
               placeholder="{#contact_phone_placeholder#}" class="form-control" maxlength="20" />
    </div>

    {* Sélecteur de destinataire *}
    {* Ce select n'est pas obligatoire et peut être désactivé *}
    {if isset($contact_services) && $contact_services|count > 1}
        <div class="form-group mb-3">
            <label for="service" class="form-label fw-bold">
                {#contact_service_label#} <span class="text-danger">*</span>
            </label>
            <select id="service" name="msg[id_contact]" class="form-select" required>
                <option value="" disabled selected>{#contact_service_placeholder#}</option>
                {foreach $contact_services as $service}
                    <option value="{$service.id_contact}">{$service.name_contact}</option>
                {/foreach}
            </select>
        </div>
    {else}
        <input type="hidden" name="msg[id_contact]" value="{$contact_services[0].id_contact|default:0}">
    {/if}

    <div class="form-group mb-3">
        <label for="subject" class="form-label fw-bold">
            {#contact_subject_label#} <span class="text-danger">*</span>
        </label>
        <input id="subject" type="text" name="msg[subject]"
               placeholder="{#contact_subject_placeholder#}" class="form-control" required/>
    </div>

    <div class="form-group mb-4">
        <label for="msg_content" class="form-label fw-bold">
            {#contact_message_label#} <span class="text-danger">*</span>
        </label>
        <textarea id="msg_content" name="msg[content]" rows="5"
                  placeholder="{#contact_message_placeholder#}" class="form-control" required></textarea>
    </div>

    <div class="mb-3 form-check">
        <input type="checkbox" class="form-check-input" id="rgpd" name="msg[rgpd]" required>
        <label class="form-check-label small text-muted" for="rgpd">
            {#contact_rgpd_label#} <span class="text-danger">*</span>
        </label>
    </div>

    <div class="mc-message"></div>

    <div class="d-grid mt-4">
        <button type="submit" class="btn btn-primary btn-lg px-5">
            <i class="bi bi-send me-2"></i> {#contact_btn_send#}
        </button>
    </div>
</form>