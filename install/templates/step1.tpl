{extends file="layout.tpl"}

{block name="content"}
    <h3 class="fw-bold mb-4">Vérification du serveur</h3>
    <p class="text-muted mb-4">Avant de procéder à l'installation, nous devons vérifier que votre serveur est compatible avec Magix CMS.</p>

    {* Vérification PHP *}
    <h5 class="fw-bold fs-6 mt-4">Version PHP</h5>
    <ul class="list-group mb-4 shadow-sm">
        <li class="list-group-item d-flex justify-content-between align-items-center">
            <span>PHP 8.2 ou supérieur requis (Actuel : <strong>{$php_version}</strong>)</span>
            {if $php_ok}
                <span class="badge bg-success rounded-pill"><i class="bi bi-check-lg"></i> OK</span>
            {else}
                <span class="badge bg-danger rounded-pill"><i class="bi bi-x-lg"></i> Échec</span>
            {/if}
        </li>
    </ul>

    {* Vérification Extensions *}
    <h5 class="fw-bold fs-6">Extensions PHP</h5>
    <ul class="list-group mb-4 shadow-sm">
        {foreach $extensions as $name => $is_ok}
            <li class="list-group-item d-flex justify-content-between align-items-center">
                {$name}
                {if $is_ok}
                    <span class="badge bg-success rounded-pill"><i class="bi bi-check-lg"></i> OK</span>
                {else}
                    <span class="badge bg-danger rounded-pill"><i class="bi bi-x-lg"></i> Manquant</span>
                {/if}
            </li>
        {/foreach}
    </ul>

    {* Vérification Dossiers *}
    <h5 class="fw-bold fs-6">Droits d'écriture</h5>
    <ul class="list-group mb-4 shadow-sm">
        {foreach $folders as $name => $is_writable}
            <li class="list-group-item d-flex justify-content-between align-items-center">
                <code>{$name}</code>
                {if $is_writable}
                    <span class="badge bg-success rounded-pill"><i class="bi bi-check-lg"></i> Accessible</span>
                {else}
                    <span class="badge bg-danger rounded-pill"><i class="bi bi-x-lg"></i> Verrouillé</span>
                {/if}
            </li>
        {/foreach}
    </ul>

    {* Bouton de validation *}
    <div class="d-flex justify-content-end mt-5 pt-3 border-top">
        {if $can_continue}
            <a href="index.php?step=2" class="btn btn-primary px-4 py-2 fw-bold">
                Suivant <i class="bi bi-arrow-right ms-2"></i>
            </a>
        {else}
            <div class="text-end">
                <p class="text-danger small fw-bold mb-2">Veuillez corriger les erreurs ci-dessus pour continuer.</p>
                <a href="index.php" class="btn btn-outline-secondary px-4 py-2">
                    <i class="bi bi-arrow-clockwise me-2"></i> Revérifier
                </a>
            </div>
        {/if}
    </div>
{/block}