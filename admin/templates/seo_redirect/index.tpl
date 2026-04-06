{extends file="layout.tpl"}
{block name='head:title'}Gestion des redirections (301/302){/block}

{block name="article"}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800"><i class="bi bi-sign-turn-right me-2"></i> Redirections SEO</h1>

        <div>
            <a href="index.php?controller=SeoRedirect&action=edit" class="btn btn-outline-primary me-2">
                <i class="bi bi-plus-lg"></i> Nouvelle redirection
            </a>
            <a href="index.php?controller=SeoRedirect&action=add" class="btn btn-success">
                <i class="bi bi-file-earmark-spreadsheet me-1"></i> Import Massif
            </a>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-header bg-white py-3 d-flex align-items-center justify-content-between">
            <h6 class="m-0 fw-bold text-primary">Liste des redirections actives</h6>
        </div>
        <div class="card-body">
            {* On utilise le helper Magix CMS pour générer le tableau *}
            {include file="components/table-forms.tpl" data=$redirect_list idcolumn=$idcolumn activation=true sortable=$sortable controller="SeoRedirect" change_offset=true}
        </div>
    </div>
{/block}