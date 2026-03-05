{extends file="layout.tpl"}
{block name='head:title'}Éditer un administrateur{/block}

{block name='article'}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="bi bi-pencil-square me-2"></i> Éditer le compte : <span class="text-primary">{$employee.firstname_admin} {$employee.lastname_admin}</span>
        </h1>
        <a href="index.php?controller=Employee" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left"></i> Retour à la liste
        </a>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body p-4">
            <form action="index.php?controller=Employee&action=edit" method="post" class="validate_form edit_form">
                <input type="hidden" name="hashtoken" value="{$hashtoken}">
                <input type="hidden" name="id_admin" value="{$employee.id_admin}">

                {* --- INFORMATIONS PERSONNELLES --- *}
                <h5 class="mb-3 fw-bold text-primary border-bottom pb-2">Informations personnelles</h5>
                <div class="row mb-4">
                    <div class="col-md-2 mb-3">
                        <label for="title_admin" class="form-label fw-medium">Civilité</label>
                        <select name="title_admin" id="title_admin" class="form-select">
                            <option value="m" {if $employee.title_admin == 'm'}selected{/if}>Monsieur</option>
                            <option value="w" {if $employee.title_admin == 'w'}selected{/if}>Madame</option>
                        </select>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="firstname_admin" class="form-label fw-medium">Prénom</label>
                        <input type="text" id="firstname_admin" name="firstname_admin" class="form-control" value="{$employee.firstname_admin}" required>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="lastname_admin" class="form-label fw-medium">Nom</label>
                        <input type="text" id="lastname_admin" name="lastname_admin" class="form-control" value="{$employee.lastname_admin}" required>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="pseudo_admin" class="form-label fw-medium">Pseudo (Optionnel)</label>
                        <input type="text" id="pseudo_admin" name="pseudo_admin" class="form-control" value="{$employee.pseudo_admin|default:''}">
                    </div>
                </div>

                {* --- COORDONNÉES & ADRESSE --- *}
                <h5 class="mb-3 fw-bold text-primary border-bottom pb-2">Coordonnées & Adresse</h5>
                <div class="row mb-4 bg-light p-3 rounded border">
                    <div class="col-md-4 mb-3">
                        <label for="phone_admin" class="form-label fw-medium">Téléphone</label>
                        <div class="input-group">
                            <span class="input-group-text bg-white"><i class="bi bi-telephone text-muted"></i></span>
                            <input type="text" id="phone_admin" name="phone_admin" class="form-control" value="{$employee.phone_admin|default:''}">
                        </div>
                    </div>
                    <div class="col-md-8 mb-3">
                        <label for="address_admin" class="form-label fw-medium">Adresse complète</label>
                        <input type="text" id="address_admin" name="address_admin" class="form-control" value="{$employee.address_admin|default:''}">
                    </div>
                    <div class="col-md-4 mb-3 mb-md-0">
                        <label for="postcode_admin" class="form-label fw-medium">Code postal</label>
                        <input type="text" id="postcode_admin" name="postcode_admin" class="form-control" value="{$employee.postcode_admin|default:''}">
                    </div>
                    <div class="col-md-4 mb-3 mb-md-0">
                        <label for="city_admin" class="form-label fw-medium">Ville</label>
                        <input type="text" id="city_admin" name="city_admin" class="form-control" value="{$employee.city_admin|default:''}">
                    </div>
                    <div class="col-md-4">
                        <label for="country_admin" class="form-label fw-medium">Pays</label>
                        <input type="text" id="country_admin" name="country_admin" class="form-control" value="{$employee.country_admin|default:''}">
                    </div>
                </div>

                {* --- IDENTIFIANTS DE CONNEXION --- *}
                <h5 class="mb-3 fw-bold text-primary border-bottom pb-2">Identifiants de connexion</h5>
                <div class="row mb-4">
                    <div class="col-md-6 mb-3 mb-md-0">
                        <label for="email_admin" class="form-label fw-medium">Adresse E-mail <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="bi bi-envelope text-muted"></i></span>
                            <input type="email" id="email_admin" name="email_admin" class="form-control" value="{$employee.email_admin}" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label for="passwd_admin" class="form-label fw-medium">Nouveau mot de passe</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="bi bi-key text-muted"></i></span>
                            <input type="password" id="passwd_admin" name="passwd_admin" class="form-control">
                        </div>
                        <div class="form-text text-muted small mt-1">
                            <i class="bi bi-info-circle me-1"></i> Laissez vide pour conserver le mot de passe actuel.
                        </div>
                    </div>
                </div>

                {* --- DROITS D'ACCÈS --- *}
                <h5 class="mb-3 fw-bold text-primary border-bottom pb-2">Droits d'accès</h5>
                <div class="row mb-4">
                    <div class="col-md-6 mb-3">
                        <label for="id_role" class="form-label fw-medium">Rôle assigné <span class="text-danger">*</span></label>
                        {if $employee.id_admin == 1}
                            <input type="hidden" name="id_role" value="1">
                            <select class="form-select" disabled>
                                <option>Super Administrateur (Intouchable)</option>
                            </select>
                            <div class="form-text text-warning small mt-1"><i class="bi bi-exclamation-triangle"></i> Le rôle du créateur ne peut être modifié.</div>
                        {else}
                            <select name="id_role" id="id_role" class="form-select" required>
                                <option value="">-- Choisir un rôle --</option>
                                {if isset($roles)}
                                    {foreach $roles as $role}
                                        <option value="{$role.id_role}" {if isset($employee.id_role) && $employee.id_role == $role.id_role}selected{/if}>
                                            {$role.role_name|capitalize}
                                        </option>
                                    {/foreach}
                                {/if}
                            </select>
                        {/if}
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-medium">Statut du compte</label>
                        <div class="form-check form-switch fs-5 mt-1">
                            <input type="hidden" name="active_admin" value="0">
                            <input class="form-check-input" type="checkbox" role="switch" id="active_admin" name="active_admin" value="1" {if $employee.active_admin == 1}checked="checked"{/if} {if $employee.id_admin == 1}disabled{/if}>
                            <label class="form-check-label fs-6 text-muted" for="active_admin">Compte Actif</label>
                            {if $employee.id_admin == 1}
                                <input type="hidden" name="active_admin" value="1">
                            {/if}
                        </div>
                    </div>
                </div>

                <hr class="my-4">
                <div class="d-flex justify-content-end">
                    <button type="submit" name="action" value="save" class="btn btn-primary px-5">
                        <i class="bi bi-save me-2"></i> Enregistrer les modifications
                    </button>
                </div>
            </form>
        </div>
    </div>
{/block}