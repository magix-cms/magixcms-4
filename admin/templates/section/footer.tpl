<footer class="py-3 bg-body-tertiary border-top flex-shrink-0">
    <div class="container-fluid text-center">
        {* On assigne l'année en cours à une variable pour que ce soit propre *}
        {assign var="current_year" value=$smarty.now|date_format:"%Y"}

        <p class="mb-0 text-muted small">
            <i class="bi bi-copyright"></i> 2008{if $current_year != '2008'} - {$current_year}{/if}
            <a href="https://www.magix-cms.com/" class="text-muted text-decoration-none" target="_blank">Magix CMS</a>

            {* 🟢 AJOUT : Récupération directe depuis mc_settings *}
            <span class="badge bg-secondary ms-1">
                        v{$mc_settings.version.value|default:'4.0.0'}
                    </span>
            &mdash; Tous droits réservés.
        </p>
    </div>
</footer>