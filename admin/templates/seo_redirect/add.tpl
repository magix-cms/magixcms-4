{extends file="layout.tpl"}
{block name='head:title'}Ajout massif de redirections{/block}

{block name='article'}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="bi bi-sign-turn-right me-2"></i> Ajout massif de redirections
        </h1>
        <a href="index.php?controller=SeoRedirect" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left"></i> Retour à la liste
        </a>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body p-4">

            <div class="alert alert-info border-0 shadow-sm d-flex align-items-center mb-4">
                <i class="bi bi-info-circle fs-4 me-3"></i>
                <div>
                    <strong>Comment importer vos URLs ?</strong><br>
                    Copiez-collez vos URLs depuis Excel ou écrivez-les manuellement, une par ligne.<br>
                    Format attendu : <code>/ancienne-url/ ; /nouvelle-url/</code> (le séparateur peut être une tabulation, une virgule ou un point-virgule).
                </div>
            </div>

            <form action="index.php?controller=SeoRedirect&action=add" method="post" class="validate_form add_form">
                <input type="hidden" name="hashtoken" value="{$hashtoken}">

                <div class="row mb-4 bg-light p-3 rounded border">
                    <div class="col-12 mb-3">
                        <label for="default_type" class="form-label fw-medium">Type de redirection par défaut</label>
                        <select name="default_type" id="default_type" class="form-select w-auto">
                            <option value="301" selected>301 - Permanente (Recommandé SEO)</option>
                            <option value="302">302 - Temporaire</option>
                        </select>
                    </div>

                    <div class="col-12 mb-3">
                        <label for="mass_redirects" class="form-label fw-medium">Collez vos redirections ici <span class="text-danger">*</span></label>
                        <textarea name="mass_redirects" id="mass_redirects" class="form-control font-monospace" rows="10" placeholder="/ancienne-page.html ; /nouvelle-page/&#10;/vieux-produit/ ; /nouveau-produit/&#10;/categorie/ ; https://www.autre-site.com/" required></textarea>
                    </div>
                </div>

                <div class="text-end">
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-save me-1"></i> Importer les redirections
                    </button>
                </div>
            </form>
        </div>
    </div>
{/block}