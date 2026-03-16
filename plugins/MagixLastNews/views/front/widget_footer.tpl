{* Fichier : plugins/MagixLastNews/views/front/widget_footer.tpl *}
<div class="col-12 col-md-6 col-lg-4 mb-4">
    <h5 class="text-uppercase mb-4 fw-bold text-white border-bottom border-secondary pb-2">
        Dernières actualités
    </h5>

    <div class="d-flex flex-column gap-3">
        {if isset($footer_news) && $footer_news|count > 0}
            {foreach $footer_news as $news}
                <article>
                    {* On ajoute la classe "news-footer-link" pour cibler ce bloc précis en SCSS *}
                    <a href="{$base_url}{$news.url|escape}" class="news-footer-link text-decoration-none d-block">

                        {* On enlève "text-white" et on met "news-title" *}
                        <h6 class="news-title mb-1 fw-bold">
                            {$news.name|escape}
                        </h6>

                        <p class="text-white-50 small mb-1 text-truncate" style="max-width: 100%;">
                            {if !empty($news.resume)}
                                {$news.resume|strip_tags|truncate:70:"..."}
                            {else}
                                {$news.content|strip_tags|truncate:70:"..."}
                            {/if}
                        </p>

                        <small class="text-muted" style="font-size: 0.75rem;">
                            <i class="bi bi-calendar3 me-1"></i>
                            {if !empty($news.date_start)}
                                {$news.date_start|date_format:"%d/%m/%Y"}
                            {else}
                                {$news.date_publish|date_format:"%d/%m/%Y"}
                            {/if}
                        </small>
                    </a>
                </article>
            {/foreach}
        {else}
            <p class="text-white-50 small">Aucune actualité pour le moment.</p>
        {/if}
    </div>
</div>