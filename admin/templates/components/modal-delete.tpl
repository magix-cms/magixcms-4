{if !isset($info_text)}{$info_text = true}{/if}
{if !isset($delete_message)}{$delete_message = {#modal_delete_message#}}{/if}
{if !isset($title)}{$title = {#modal_delete_title#}}{/if}
{if !isset($controller)}{$controller = $smarty.get.controller}{/if}

<div class="modal fade" id="delete_modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title d-flex align-items-center">
                    <i class="bi bi-trash3 me-2"></i> {#modal_delete_title#|default:'Suppression'}
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4 text-center">
                <i class="bi bi-exclamation-octagon text-danger display-4 mb-3 d-block"></i>
                <p class="fs-5 fw-bold mb-1">{#modal_delete_message#|default:'Êtes-vous sûr ?'}</p>
                <p class="text-muted small mb-0">{#modal_delete_info#|default:'Cette action est irréversible.'}</p>
            </div>
            <div class="modal-footer bg-light justify-content-center border-0">
                <button type="button" class="btn btn-outline-secondary px-4" data-bs-dismiss="modal">{#cancel#|ucfirst}</button>
                <button type="button" id="confirm_delete_btn"
                        data-url="index.php?controller={$smarty.get.controller}&action=delete&hashtoken={$url_token}"
                        class="btn btn-danger px-4 fw-bold">
                    {#remove#|ucfirst}
                </button>
            </div>
        </div>
    </div>
</div>