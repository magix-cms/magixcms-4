{extends file="layout.tpl"}

{block name='head:title'}{$seo_title}{/block}
{block name='head:description'}{$seo_desc}{/block}

{block name="styleSheet" append}
    {$page_css = ["news"] scope="parent"}
{/block}

{block name="article"}
    <div class="container py-5" id="magix-calendar-container">
        <header class="mb-4">
            <a href="{$base_url}{$current_lang.iso_lang}/news/" class="btn btn-sm btn-outline-secondary mb-3">
                <i class="bi bi-arrow-left"></i> {#news_back_to_list#}
            </a>

            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center">
                <h1 class="h2 mb-3 mb-md-0" id="calendar-month-year">{$seo_title|replace:"Agenda : ":""}</h1>
                <div class="btn-group shadow-sm">
                    <button class="btn btn-outline-primary" id="prev-month" aria-label="Précédent"><i class="bi bi-chevron-left"></i></button>
                    <button class="btn btn-outline-primary" id="today-btn">{#news_calendar_today#}</button>
                    <button class="btn btn-outline-primary" id="next-month" aria-label="Suivant"><i class="bi bi-chevron-right"></i></button>
                </div>
            </div>
        </header>

        <div class="calendar-header-days d-none d-md-grid grid-7-cols text-center fw-bold mb-2" style="display: grid; grid-template-columns: repeat(7, 1fr);">
            {foreach $days_of_week as $dayName}
                <div>{$dayName|replace:'.':''}</div>
            {/foreach}
        </div>

        <div id="calendar-grid" class="calendar-grid rounded shadow-sm overflow-hidden">
            {* Le contenu sera généré dynamiquement par la classe Vanilla JS *}
        </div>
    </div>
{/block}

{block name="javascript_data"}
    {* Chargement propre du composant JS *}
    {$page_js = [
    'defer' => ['MagixCalendar']
    ] scope="parent"}
{/block}

{block name="javascript" append}
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            if (typeof MagixCalendar !== 'undefined') {
                const calendar = new MagixCalendar({
                    containerId: 'calendar-grid',
                    titleId: 'calendar-month-year',
                    currentYear: {$current_year},
                    currentMonth: {$current_month},
                    events: {$events|json_encode nofilter},
                    lang: '{$current_lang.iso_lang}'
                });
                calendar.init();
            } else {
                console.error("Erreur Magix CMS : La classe MagixCalendar est introuvable.");
            }
        });
    </script>
{/block}