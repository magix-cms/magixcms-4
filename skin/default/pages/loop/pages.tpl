{foreach $pages.subdata as $child}
    <div class="col-md-4 mb-4">
        <div class="card h-100 shadow-sm border-0 transition-hover">
            <a href="{$child.url}" class="text-decoration-none text-dark">
                <div class="card-img-top overflow-hidden">
                    {include file="components/img.tpl" img=$child.img class="img-fluid w-100"}
                </div>
                <div class="card-body">
                    <h5 class="card-title fw-bold text-primary">{$child.name}</h5>
                    {$description = $child.resume|default:$child.content|strip_tags|truncate:120:"..."}
                    {if $description}<p class="card-text small text-muted">{$description}</p>{/if}
                </div>
                <div class="card-footer bg-transparent border-0 pt-0 pb-3 text-end">
                    <span class="text-primary small fw-bold">{#read_more#|default:'Lire la suite'} <i class="bi bi-arrow-right"></i></span>
                </div>
            </a>
        </div>
    </div>
{/foreach}