{extends file="layout.tpl"}
{block name='head:title'}{#news_management#}{/block}
{block name='body:id'}news{/block}

{block name="article"}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="bi bi-newspaper me-2"></i> {#news_management#}
        </h1>
        <a href="index.php?controller=News&action=add" class="btn btn-primary shadow-sm">
            <i class="bi bi-plus-lg me-1"></i> {#add_news#}
        </a>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-header bg-white py-3 d-flex align-items-center justify-content-between border-bottom">
            <h6 class="m-0 fw-bold text-primary">Liste des actualités</h6>

            {*<form action="index.php" method="get" class="d-flex mb-0">
                <input type="hidden" name="controller" value="News">
                <div class="input-group input-group-sm">
                    <input type="text" class="form-control" name="search[name_news]" value="{$get_search.name_news|default:''}" placeholder="Rechercher un titre...">
                    <button class="btn btn-outline-secondary" type="submit">
                        <i class="bi bi-search"></i>
                    </button>
                </div>
            </form>*}
        </div>

        <div class="card-body p-4">
            {include file="components/table-forms.tpl" data=$news_list idcolumn=$idcolumn activation=true sortable=$sortable controller="News" change_offset=true}
        </div>
    </div>
{/block}