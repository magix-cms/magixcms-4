{extends file="layout.tpl"}

{block name='head:title'}Gestion des Thèmes{/block}

{block name='article'}
    <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
        <h1 class="h2 mb-0"><i class="bi bi-brush me-2 text-muted"></i>Gestion des Thèmes (Skins)</h1>
    </div>

    <div class="row g-4">
        {if isset($themes) && $themes|count > 0}
            {foreach $themes as $theme}
                <div class="col-12 col-md-6 col-lg-4 col-xl-3">
                    <div class="card border-0 shadow-sm h-100 {if $theme.is_active}border border-primary border-3{/if}" style="transition: transform 0.2s;">

                        {* Vignette du thème *}
                        <img src="{$theme.preview}" class="card-img-top" alt="Aperçu du thème {$theme.name}" style="height: 220px; object-fit: cover; border-bottom: 1px solid #eee;">

                        <div class="card-body d-flex justify-content-between align-items-center bg-light">
                            <h5 class="card-title mb-0 fw-bold text-capitalize">{$theme.name}</h5>

                            {if $theme.is_active}
                                <span class="badge bg-success px-3 py-2"><i class="bi bi-check-circle me-1"></i> Actif</span>
                            {else}
                                <button class="btn btn-outline-primary btn-sm btn-activate-theme" data-theme="{$theme.name}">
                                    <i class="bi bi-palette me-1"></i> Activer
                                </button>
                            {/if}
                        </div>
                    </div>
                </div>
            {/foreach} {* 🟢 LA CORRECTION EST ICI 🟢 *}
        {else}
            <div class="col-12">
                <div class="alert alert-warning border-0 d-flex align-items-center shadow-sm">
                    <i class="bi bi-exclamation-triangle fs-4 me-3"></i>
                    Aucun thème n'a été trouvé dans le dossier <strong>/skin/</strong> de votre CMS.
                </div>
            </div>
        {/if}
    </div>
{/block}

{block name='javascripts' append}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const btns = document.querySelectorAll('.btn-activate-theme');

            btns.forEach(btn => {
                btn.addEventListener('click', function() {
                    const themeName = this.dataset.theme;
                    const token = '{$hashtoken}';

                    // Animation du bouton pendant le chargement
                    const originalText = this.innerHTML;
                    this.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>';
                    this.disabled = true;

                    const formData = new FormData();
                    formData.append('theme', themeName);
                    formData.append('hashtoken', token);

                    fetch('index.php?controller=Theme&action=activate', {
                        method: 'POST',
                        body: formData
                    })
                        .then(response => response.json())
                        .then(data => {
                            if (data.status) {
                                if (typeof MagixToast !== 'undefined') {
                                    MagixToast.success(data.message);
                                }
                                // Rechargement doux de la page pour mettre à jour les badges
                                setTimeout(() => { window.location.reload(); }, 1200);
                            } else {
                                if (typeof MagixToast !== 'undefined') {
                                    MagixToast.error(data.message);
                                }
                                this.innerHTML = originalText;
                                this.disabled = false;
                            }
                        })
                        .catch(error => {
                            console.error('Erreur AJAX:', error);
                            this.innerHTML = originalText;
                            this.disabled = false;
                        });
                });
            });
        });
    </script>
{/block}