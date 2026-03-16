<section class="py-5 bg-light">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="h3 fw-bold mb-0">À la une</h2>
            <a href="{$base_url}{$current_lang.iso_lang}/news/" class="btn btn-outline-primary btn-sm">Voir tout</a>
        </div>

        {*<div class="row g-4">
            {foreach $last_news as $item}
                <div class="col-md-4">
                    <div class="card h-100 border-0 shadow-sm">
                        <a href="{$item.url}">
                            {include file="components/img.tpl" img=$item.img class="card-img-top" size="medium" responsiveC=true lazy=true}
                        </a>
                        <div class="card-body">
                            {if !empty($item.date_start)}
                                <span class="badge bg-warning text-dark mb-2">Évènement</span>
                            {else}
                                <span class="badge bg-primary mb-2">Actualité</span>
                            {/if}
                            <h4 class="h6 fw-bold"><a href="{$item.url}" class="text-dark text-decoration-none">{$item.name}</a></h4>
                            <p class="small text-muted mb-0">{$item.resume|strip_tags|truncate:80:"..."}</p>
                        </div>
                    </div>
                </div>
            {/foreach}
        </div>*}
        {include file="news/loop/news-grid.tpl" data=$last_news}
    </div>
</section>