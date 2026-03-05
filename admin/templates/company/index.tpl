{extends file="layout.tpl"}

{block name='head:title'}Configuration Entreprise{/block}

{block name='article'}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="bi bi-building-gear me-2"></i> Configuration Entreprise
        </h1>
    </div>

    <form class="validate_form" action="index.php?controller=Company" method="post">
        <input type="hidden" name="hashtoken" value="{$hashtoken}">

        <div class="card shadow-sm border-0">
            <div class="card-header bg-white p-0">
                <ul class="nav nav-tabs nav-fill" id="configTab" role="tablist">
                    <li class="nav-item">
                        <button class="nav-link active py-3 fw-bold" data-bs-toggle="tab" data-bs-target="#general_pane" type="button">
                            <i class="bi bi-info-circle me-2"></i> Général
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link py-3 fw-bold" data-bs-toggle="tab" data-bs-target="#contact_pane" type="button">
                            <i class="bi bi-envelope-at me-2"></i> Coordonnées
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link py-3 fw-bold" data-bs-toggle="tab" data-bs-target="#social_pane" type="button">
                            <i class="bi bi-share me-2"></i> Réseaux Sociaux
                        </button>
                    </li>
                </ul>
            </div>

            <div class="card-body p-4">
                <div class="tab-content">

                    {* --- ONGLET 1 : GÉNÉRAL --- *}
                    <div class="tab-pane fade show active" id="general_pane" role="tabpanel">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-medium">Nom de la société</label>
                                <input type="text" class="form-control" name="company[name]" value="{$company_data.name|default:''}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-medium">Numéro de TVA / Siret</label>
                                <input type="text" class="form-control" name="company[tva]" value="{$company_data.tva|default:''}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-medium">Type d'entité (Schema.org)</label>
                                <select class="form-select" name="company[type]">
                                    {* Boucle sur le tableau envoyé par le contrôleur *}
                                    {foreach $company_types as $key => $type}
                                        <option value="{$key}"
                                                {if $company_data.type|default:'org' == $key}selected{/if}>
                                            {$type.label}
                                        </option>
                                    {/foreach}
                                </select>
                                {* Petit bonus : afficher le schéma technique en gris pour info *}
                                <div class="form-text text-muted small">
                                    Schema actuel :
                                    <span class="fw-bold">
                                        {$company_types[$company_data.type|default:'org'].schema}
                                    </span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-medium">Mode Boutique (E-shop)</label>
                                <div class="form-check form-switch mt-2">
                                    <input type="hidden" name="company[eshop]" value="0">
                                    <input class="form-check-input" type="checkbox" name="company[eshop]" value="1" {if $company_data.eshop|default:'0' == '1'}checked{/if}>
                                    <label class="form-check-label">Activer les fonctionnalités e-commerce</label>
                                </div>
                            </div>
                        </div>
                    </div>

                    {* --- ONGLET 2 : COORDONNÉES --- *}
                    <div class="tab-pane fade" id="contact_pane" role="tabpanel">
                        <div class="row g-3">
                            {* Adresse *}
                            <div class="col-12">
                                <h6 class="text-primary fw-bold border-bottom pb-2 mb-3">Adresse postale</h6>
                            </div>
                            <div class="col-md-8">
                                <label class="form-label">Rue et numéro</label>
                                <input type="text" class="form-control" name="company[street]" value="{$company_data.street|default:''}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Complément (Boîte...)</label>
                                <input type="text" class="form-control" name="company[adress]" value="{$company_data.adress|default:''}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Code Postal</label>
                                <input type="text" class="form-control" name="company[postcode]" value="{$company_data.postcode|default:''}">
                            </div>
                            <div class="col-md-8">
                                <label class="form-label">Ville</label>
                                <input type="text" class="form-control" name="company[city]" value="{$company_data.city|default:''}">
                            </div>

                            {* Contact *}
                            <div class="col-12 mt-4">
                                <h6 class="text-primary fw-bold border-bottom pb-2 mb-3">Contact direct</h6>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Email de contact</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                                    <input type="email" class="form-control" name="company[mail]" value="{$company_data.mail|default:''}">
                                </div>
                                <div class="form-check form-switch mt-2 small">
                                    <input type="hidden" name="company[crypt_mail]" value="0">
                                    <input class="form-check-input" type="checkbox" name="company[crypt_mail]" value="1" {if $company_data.crypt_mail|default:'1' == '1'}checked{/if}>
                                    <label class="form-check-label text-muted">Crypter l'email sur le site (Anti-spam)</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Téléphone</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-telephone"></i></span>
                                    <input type="text" class="form-control" name="company[phone]" value="{$company_data.phone|default:''}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Mobile</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-phone"></i></span>
                                    <input type="text" class="form-control" name="company[mobile]" value="{$company_data.mobile|default:''}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Fax</label>
                                <input type="text" class="form-control" name="company[fax]" value="{$company_data.fax|default:''}">
                            </div>
                        </div>
                    </div>

                    {* --- ONGLET 3 : RÉSEAUX SOCIAUX --- *}
                    <div class="tab-pane fade" id="social_pane" role="tabpanel">
                        <div class="alert alert-info small">
                            <i class="bi bi-info-circle me-1"></i> Laissez vide pour ne pas afficher l'icône sur le site.
                        </div>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label"><i class="bi bi-facebook text-primary me-1"></i> Facebook</label>
                                <input type="url" class="form-control" name="company[facebook]" value="{$company_data.facebook|default:''}" placeholder="https://...">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label"><i class="bi bi-instagram text-danger me-1"></i> Instagram</label>
                                <input type="text" class="form-control" name="company[instagram]" value="{$company_data.instagram|default:''}" placeholder="@votre_compte ou URL">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label"><i class="bi bi-linkedin text-primary me-1"></i> LinkedIn</label>
                                <input type="url" class="form-control" name="company[linkedin]" value="{$company_data.linkedin|default:''}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label"><i class="bi bi-twitter-x text-dark me-1"></i> X (Twitter)</label>
                                <input type="text" class="form-control" name="company[twitter]" value="{$company_data.twitter|default:''}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label"><i class="bi bi-youtube text-danger me-1"></i> Youtube</label>
                                <input type="url" class="form-control" name="company[youtube]" value="{$company_data.youtube|default:''}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label"><i class="bi bi-github text-dark me-1"></i> Github</label>
                                <input type="url" class="form-control" name="company[github]" value="{$company_data.github|default:''}">
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            <div class="card-footer bg-white py-3 text-end border-top">
                <button type="submit" class="btn btn-primary px-5">
                    <i class="bi bi-save me-2"></i> Enregistrer la configuration
                </button>
            </div>
        </div>
    </form>
{/block}

{block name="javascripts" append}
    <script src="templates/js/MagixForms.min.js?v={$smarty.now}"></script>
{/block}