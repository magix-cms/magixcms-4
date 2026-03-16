<div class="col-12 col-md-6 col-lg-4 mb-4">
    <h5 class="text-uppercase mb-4 fw-bold text-white border-bottom border-secondary pb-2">Suivez-nous</h5>

    <div class="d-flex flex-wrap gap-3">
        {if !empty($companyData.facebook)}
            <a href="{$companyData.facebook}" target="_blank" class="social-icon-link facebook" title="Facebook">
                <i class="bi bi-facebook"></i>
            </a>
        {/if}

        {if !empty($companyData.twitter)}
            <a href="{$companyData.twitter}" target="_blank" class="social-icon-link twitter" title="X (Twitter)">
                <i class="bi bi-twitter-x"></i>
            </a>
        {/if}

        {if !empty($companyData.instagram)}
            <a href="{$companyData.instagram}" target="_blank" class="social-icon-link instagram" title="Instagram">
                <i class="bi bi-instagram"></i>
            </a>
        {/if}

        {if !empty($companyData.linkedin)}
            <a href="{$companyData.linkedin}" target="_blank" class="social-icon-link linkedin" title="LinkedIn">
                <i class="bi bi-linkedin"></i>
            </a>
        {/if}

        {if !empty($companyData.youtube)}
            <a href="{$companyData.youtube}" target="_blank" class="social-icon-link youtube" title="YouTube">
                <i class="bi bi-youtube"></i>
            </a>
        {/if}

        {if !empty($companyData.github)}
            <a href="{$companyData.github}" target="_blank" class="social-icon-link github" title="GitHub">
                <i class="bi bi-github"></i>
            </a>
        {/if}
    </div>
</div>