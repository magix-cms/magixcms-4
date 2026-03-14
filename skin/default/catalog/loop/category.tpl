{foreach $category.subdata as $child}
    <div class="col-6 col-md-4 col-lg-3 mb-4">
        <div class="card h-100 shadow-sm border-0 transition-hover text-center bg-white">
            <a href="{$child.url}" class="text-decoration-none text-dark p-3 d-block">
                <div class="card-img-top overflow-hidden mb-3">
                    {include file="components/img.tpl" img=$child.img class="img-fluid rounded" size="small"}
                </div>
                <h5 class="card-title fw-bold text-primary mb-0 fs-6">{$child.name}</h5>
            </a>
        </div>
    </div>
{/foreach}