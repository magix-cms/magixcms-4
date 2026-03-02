<div class="tab-pane fade" id="subpages" role="tabpanel">
    <div class="card shadow-sm">
        <div class="card-body p-0">
            {* On réutilise le composant de tableau en lui passant les enfants (subdata) *}
            {include file="components/table-forms.tpl" data=$subdata checkbox=true sortable=true dlt=true}
        </div>
    </div>
</div>