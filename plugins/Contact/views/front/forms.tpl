<form id="contact-form" class="validate_form" method="post" action="{$base_url}{$current_lang.iso_lang}/contact/send">

    {* Champ de sécurité anti-CSRF (optionnel mais recommandé) *}
    <input type="hidden" name="csrf_token" value="{$csrf_token|default:''}">

    <div class="row g-3">
        <div class="col-12 col-md-6">
            <div class="form-group mb-3">
                <label for="firstname" class="form-label fw-bold">Prénom <span class="text-danger">*</span></label>
                <input id="firstname" type="text" name="msg[firstname]" placeholder="Votre prénom" class="form-control" required/>
            </div>
        </div>
        <div class="col-12 col-md-6">
            <div class="form-group mb-3">
                <label for="lastname" class="form-label fw-bold">Nom <span class="text-danger">*</span></label>
                <input id="lastname" type="text" name="msg[lastname]" placeholder="Votre nom" class="form-control" required/>
            </div>
        </div>
    </div>

    <div class="form-group mb-3">
        <label for="email" class="form-label fw-bold">E-mail <span class="text-danger">*</span></label>
        <input id="email" type="email" name="msg[email]" placeholder="votre.email@exemple.com" class="form-control" required/>
    </div>

    <div class="form-group mb-3">
        <label for="phone" class="form-label fw-bold">Téléphone</label>
        <input id="phone" type="tel" name="msg[phone]" placeholder="Votre numéro de téléphone" class="form-control" maxlength="20" />
    </div>

    {* Sélecteur de destinataire (Si plusieurs services configurés) *}
    {if isset($contact_services) && $contact_services|count > 1}
        <div class="form-group mb-3">
            <label for="service" class="form-label fw-bold">Service concerné <span class="text-danger">*</span></label>
            <select id="service" name="msg[id_contact]" class="form-select" required>
                <option value="" disabled selected>Veuillez choisir un service...</option>
                {foreach $contact_services as $service}
                    <option value="{$service.id_contact}">{$service.name_contact}</option>
                {/foreach}
            </select>
        </div>
    {else}
        {* Si un seul service, on l'envoie de manière invisible *}
        <input type="hidden" name="msg[id_contact]" value="{$contact_services[0].id_contact|default:0}">
    {/if}

    <div class="form-group mb-3">
        <label for="subject" class="form-label fw-bold">Sujet <span class="text-danger">*</span></label>
        <input id="subject" type="text" name="msg[subject]" placeholder="Sujet de votre demande" class="form-control" required/>
    </div>

    <div class="form-group mb-4">
        <label for="msg_content" class="form-label fw-bold">Message <span class="text-danger">*</span></label>
        <textarea id="msg_content" name="msg[content]" rows="5" placeholder="Comment pouvons-nous vous aider ?" class="form-control" required></textarea>
    </div>

    <div class="mb-3 form-check">
        <input type="checkbox" class="form-check-input" id="rgpd" name="msg[rgpd]" required>
        <label class="form-check-label small text-muted" for="rgpd">
            J'accepte que mes données soient utilisées pour traiter ma demande. <span class="text-danger">*</span>
        </label>
    </div>

    <div class="mc-message"></div>

    <div class="d-grid mt-4">
        <button type="submit" class="btn btn-primary btn-lg px-5">
            <i class="bi bi-send me-2"></i> Envoyer le message
        </button>
    </div>
</form>