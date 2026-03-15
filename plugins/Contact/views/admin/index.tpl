{extends file="layout.tpl"}

{block name='head:title'}Plugin Contact{/block}

{block name='article'}
    {* --- EN-TÊTE DE LA PAGE --- *}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="bi bi-envelope-paper me-2"></i> Gestion du Contact
        </h1>
    </div>

    <div class="card shadow-sm border-0">
        {* --- MENU PRINCIPAL DES ONGLETS DU PLUGIN --- *}
        <div class="card-header bg-white p-0 border-bottom-0">
            <ul class="nav nav-tabs nav-fill" id="contactTab" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active py-3 fw-bold" id="page-tab" data-bs-toggle="tab" data-bs-target="#page_pane" type="button" role="tab">
                        <i class="bi bi-file-earmark-text me-2"></i> Contenu de la Page
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link py-3 fw-bold" id="contacts-tab" data-bs-toggle="tab" data-bs-target="#contacts_pane" type="button" role="tab">
                        <i class="bi bi-people me-2"></i> Destinataires (Emails)
                    </button>
                </li>
            </ul>
        </div>

        <div class="card-body p-4">
            <div class="tab-content" id="contactTabContent">

                {* ==========================================================
                   ONGLET 1 : CONFIGURATION DE LA PAGE
                   ========================================================== *}
                <div class="tab-pane fade show active" id="page_pane" role="tabpanel">

                    <div class="card shadow-sm border-0 mb-0">
                        {* HEADER DE LA CARTE AVEC LE DROPDOWN DE LANGUE *}
                        <div class="card-header bg-white py-3 d-flex align-items-center justify-content-between border-bottom">
                            <h6 class="m-0 fw-bold text-primary">Édition de la page Contact</h6>
                            {if isset($langs)}
                                {include file="components/dropdown-lang.tpl"}
                            {/if}
                        </div>

                        <div class="card-body">
                            <form id="edit_contact_page_form" action="index.php?controller=Contact&action=savePage" method="post" class="validate_form">
                                <input type="hidden" name="hashtoken" value="{$hashtoken|default:''}">

                                <div class="tab-content">
                                    {if isset($langs)}
                                        {foreach $langs as $id => $iso}
                                            <fieldset role="tabpanel" class="tab-pane {if $iso@first}show active{/if}" id="lang-{$id}">

                                                {* LIGNE 1 : Titre H1 et Statut *}
                                                <div class="row mb-3">
                                                    <div class="col-md-9">
                                                        <label for="name_page_{$id}" class="form-label fw-medium">Titre de la page</label>
                                                        <input type="text" class="form-control" id="name_page_{$id}" name="content[{$id}][name_page]" value="{$pageData.$id.name_page|default:''}" />
                                                    </div>
                                                    <div class="col-md-3">
                                                        <label class="form-label fw-medium">Statut</label>
                                                        <div class="form-check form-switch fs-5 mt-1">
                                                            <input class="form-check-input" type="checkbox" role="switch" id="published_page_{$id}" name="content[{$id}][published_page]" value="1" {if ($pageData.$id.published_page|default:0) == 1}checked{/if} />
                                                            <label class="form-check-label fs-6 text-muted" for="published_page_{$id}">Publiée</label>
                                                        </div>
                                                    </div>
                                                </div>

                                                {* LIGNE 3 : Résumé *}
                                                <div class="mb-3">
                                                    <label for="resume_page_{$id}" class="form-label fw-medium">Texte d'introduction :</label>
                                                    <textarea class="form-control" id="resume_page_{$id}" name="content[{$id}][resume_page]" rows="2">{$pageData.$id.resume_page|default:''}</textarea>
                                                </div>

                                                {* LIGNE 4 : Contenu TinyMCE *}
                                                <div class="mb-4">
                                                    <label for="content_page_{$id}" class="form-label fw-medium">Contenu additionnel :</label>
                                                    <textarea class="form-control mceEditor" id="content_page_{$id}" name="content[{$id}][content_page]" rows="10">{$pageData.$id.content_page|default:''}</textarea>
                                                </div>

                                                {* ACCORDÉON SEO *}
                                                <div class="accordion mb-3" id="seoAccordion_{$id}">
                                                    <div class="accordion-item border-0 bg-light rounded">
                                                        <h2 class="accordion-header">
                                                            <button class="accordion-button collapsed bg-transparent shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#seo_{$id}">
                                                                <i class="bi bi-google me-2 text-primary"></i> <strong>Paramètres SEO</strong>
                                                            </button>
                                                        </h2>
                                                        <div id="seo_{$id}" class="accordion-collapse collapse" data-bs-parent="#seoAccordion_{$id}">
                                                            <div class="accordion-body bg-white border-top">
                                                                <div class="mb-2">
                                                                    <label for="seo_title_{$id}" class="form-label d-flex justify-content-between">
                                                                        Titre SEO
                                                                        <span id="count-title-{$id}" class="badge bg-success">0 / 70</span>
                                                                    </label>
                                                                    <input type="text" id="seo_title_{$id}" name="content[{$id}][seo_title_page]" class="form-control seo-counter" data-target="#count-title-{$id}" data-max="70" value="{$pageData.$id.seo_title_page|default:''}" />
                                                                </div>
                                                                <div class="mb-2">
                                                                    <label for="seo_desc_{$id}" class="form-label d-flex justify-content-between">
                                                                        Description SEO
                                                                        <span id="count-desc-{$id}" class="badge bg-success">0 / 180</span>
                                                                    </label>
                                                                    <textarea id="seo_desc_{$id}" name="content[{$id}][seo_desc_page]" class="form-control seo-counter" data-target="#count-desc-{$id}" data-max="180" rows="3">{$pageData.$id.seo_desc_page|default:''}</textarea>
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
                                    <button class="btn btn-primary px-4" type="submit">
                                        <i class="bi bi-save me-1"></i> Sauvegarder la page
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                {* ==========================================================
                   ONGLET 2 : LISTE DES DESTINATAIRES
                   ========================================================== *}
                <div class="tab-pane fade" id="contacts_pane" role="tabpanel">

                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="mb-0 text-muted small text-uppercase fw-bold">Emails destinataires</h5>
                        <button class="btn btn-sm btn-success btn-add-contact" data-bs-toggle="modal" data-bs-target="#modalContact">
                            <i class="bi bi-plus-circle me-1"></i> Ajouter un contact
                        </button>
                    </div>

                    <div class="table-responsive bg-white rounded shadow-sm border">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                            <tr>
                                <th class="ps-4">#</th>
                                <th>Nom / Service (FR)</th>
                                <th>Adresse E-mail</th>
                                <th>Par défaut</th>
                                <th class="text-end pe-4">Actions</th>
                            </tr>
                            </thead>
                            <tbody>
                            {if isset($contactsList) && $contactsList|count > 0}
                                {foreach $contactsList as $contact}
                                    <tr>
                                        <td class="ps-4 text-muted small">{$contact.id_contact}</td>
                                        <td class="fw-bold">
                                            {$contact.name_contact|default:'<em class="text-muted">Non défini</em>'}
                                            {if !$contact.published_contact} <span class="badge bg-danger ms-2">Désactivé</span> {/if}
                                        </td>
                                        <td><a href="mailto:{$contact.mail_contact}" class="text-decoration-none">{$contact.mail_contact}</a></td>
                                        <td>
                                            {if $contact.is_default}
                                                <span class="badge bg-primary">Oui</span>
                                            {else}
                                                <span class="badge bg-secondary">Non</span>
                                            {/if}
                                        </td>
                                        <td class="text-end pe-4">
                                            <button class="btn btn-sm btn-outline-primary me-1 btn-edit-contact" data-id="{$contact.id_contact}">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-danger" onclick="Magix.delete('index.php?controller=Contact&action=deleteContact&id_contact={$contact.id_contact}');">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                {/foreach}
                            {else}
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-5">
                                        <i class="bi bi-inbox fs-2 d-block mb-2 opacity-50"></i>
                                        Aucun destinataire configuré. Le formulaire de contact ne pourra pas être envoyé.
                                    </td>
                                </tr>
                            {/if}
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>
    </div>

    {* ==========================================================
       MODALE D'AJOUT / ÉDITION DE CONTACT
       ========================================================== *}
    <div class="modal fade" id="modalContact" tabindex="-1" aria-labelledby="modalContactLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content border-0 shadow">
                <div class="modal-header d-flex justify-content-between align-items-center bg-light border-bottom">
                    <h5 class="modal-title m-0 fw-bold text-primary" id="modalContactLabel"><i class="bi bi-person-plus me-2"></i> Ajouter un destinataire</h5>
                    <div class="d-flex align-items-center gap-3">
                        {if isset($langs)}
                            {include file="components/dropdown-lang.tpl" prefix="modal-" label=false}
                        {/if}
                        <button type="button" class="btn-close m-0" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                </div>

                <form id="contact_form" action="index.php?controller=Contact&action=saveContact" method="post" class="validate_form">
                    <div class="modal-body p-4">
                        <input type="hidden" name="hashtoken" value="{$hashtoken|default:''}">
                        <input type="hidden" name="id_contact" id="edit_id_contact" value="0">

                        <div class="row mb-4 bg-light p-3 rounded border">
                            <div class="col-md-8">
                                <label class="form-label fw-bold">Adresse E-mail <span class="text-danger">*</span></label>
                                <input type="email" id="edit_mail_contact" name="mail_contact" class="form-control" required placeholder="ex: contact@monsite.com">
                            </div>
                            <div class="col-md-4 pt-4 text-md-end">
                                <div class="form-check form-switch fs-5 d-inline-block mt-1">
                                    <input class="form-check-input" type="checkbox" role="switch" id="edit_is_default" name="is_default" value="1" />
                                    <label class="form-check-label fs-6 text-muted ms-2" for="edit_is_default">Contact par défaut</label>
                                </div>
                            </div>
                        </div>

                        <h6 class="fw-bold mb-3 border-bottom pb-2 text-primary">Traduction du libellé</h6>

                        <div class="tab-content bg-white p-3 border rounded" id="modal-tab-content">
                            {if isset($langs)}
                                {foreach $langs as $id => $iso}
                                    <div class="tab-pane fade {if $iso@first}show active{/if}" id="modal-lang-{$id}" role="tabpanel">
                                        <div class="mb-3">
                                            <label class="form-label fw-medium">Nom du service ou du contact ({$iso|upper})</label>
                                            <input type="text" id="edit_name_{$id}" name="contact_content[{$id}][name_contact]" class="form-control" placeholder="ex: Service Commercial">
                                        </div>
                                        <div class="form-check form-switch mt-3">
                                            {* 🟢 HTML CORRIGÉ : ID edit_status_ *}
                                            <input class="form-check-input" type="checkbox" role="switch" id="edit_status_{$id}" name="contact_content[{$id}][published_contact]" value="1" checked>
                                            <label class="form-check-label text-muted" for="edit_status_{$id}">Ce service est disponible dans cette langue</label>
                                        </div>
                                    </div>
                                {/foreach}
                            {/if}
                        </div>

                    </div>
                    <div class="modal-footer bg-light border-top">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-primary"><i class="bi bi-check-circle me-1"></i> Enregistrer le contact</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
{/block}

{block name="javascripts" append}
    <script src="templates/js/MagixFormTools.min.js?v={$smarty.now}"></script>
    <script>
        {literal}
        document.addEventListener('DOMContentLoaded', function() {
            // 1. Initialisation des compteurs SEO et du cadenas d'URL
            new MagixFormTools();

            // 2. Gestion de la modale d'édition via AJAX
            document.querySelectorAll('.btn-edit-contact').forEach(btn => {
                btn.addEventListener('click', async function() {
                    const idContact = this.getAttribute('data-id');

                    try {
                        const response = await fetch('index.php?controller=Contact&action=getContact&id_contact=' + idContact);
                        const result = await response.json();

                        // 🟢 CORRECTION ICI : "result.contact" au lieu de "result.data.contact"
                        if (result.success && result.contact) {
                            const data = result.contact; // 🟢 ET ICI

                            // A. Remplir les champs principaux
                            document.getElementById('edit_id_contact').value = data.id_contact;
                            document.getElementById('edit_mail_contact').value = data.mail_contact;
                            document.getElementById('edit_is_default').checked = (data.is_default == 1);

                            // B. Remplir les traductions (boucle sur les IDs de langue)
                            if (data.translations) {
                                document.querySelectorAll('[id^="edit_status_"]').forEach(el => el.checked = false);
                                document.querySelectorAll('[id^="edit_name_"]').forEach(el => el.value = '');

                                for (const [idLang, trans] of Object.entries(data.translations)) {
                                    const nameInput = document.getElementById('edit_name_' + idLang);
                                    const statusInput = document.getElementById('edit_status_' + idLang);

                                    if (nameInput) nameInput.value = trans.name_contact || '';
                                    if (statusInput) statusInput.checked = (trans.published_contact == 1);
                                }
                            }

                            // C. Modifier le titre de la modale et l'afficher
                            document.getElementById('modalContactLabel').innerHTML = '<i class="bi bi-pencil-square me-2"></i> Modifier le destinataire';
                            const modal = new bootstrap.Modal(document.getElementById('modalContact'));
                            modal.show();
                        } else {
                            console.error(result.message);
                        }
                    } catch (error) {
                        console.error('Erreur AJAX:', error);
                    }
                });
            });

            // 3. Reset de la modale quand on clique sur "Ajouter un contact"
            document.querySelector('.btn-add-contact').addEventListener('click', function() {
                document.getElementById('contact_form').reset();
                document.getElementById('edit_id_contact').value = '0';
                document.getElementById('modalContactLabel').innerHTML = '<i class="bi bi-person-plus me-2"></i> Ajouter un destinataire';

                document.querySelectorAll('[id^="edit_status_"]').forEach(el => el.checked = true);
            });
        });
        {/literal}
    </script>
{/block}