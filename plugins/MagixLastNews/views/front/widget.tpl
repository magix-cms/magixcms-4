<section class="py-5 bg-light">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="h3 fw-bold mb-0">À la une</h2>
            <a href="{$base_url}{$current_lang.iso_lang}/news/" class="btn btn-outline-primary btn-sm">Voir tout</a>
        </div>
        {include file="news/loop/news-grid.tpl" data=$last_news classType="normal"}
    </div>
</section>