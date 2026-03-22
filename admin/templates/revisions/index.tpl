{extends file="layout.tpl"}

{block name='head:title'}Gestion des révisions{/block}

{block name='article'}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="bi bi-clock-history me-2"></i> Historique des révisions
        </h1>
    </div>

    <div class="row">
        <div class="col-lg-8 mx-auto">

            <div class="card shadow-sm border-0 mb-4">
                <div class="card-body text-center py-5">
                    <i class="bi bi-database text-primary opacity-50" style="font-size: 4rem;"></i>
                    <h2 class="mt-3 fw-bold display-5">{$total_revisions|default:0}</h2>
                    <p class="text-muted fs-5">Révisions enregistrées en base de données</p>

                    <hr class="my-4 w-50 mx-auto">

                    <p class="text-muted small mb-4 px-md-5">
                        Le système de révisions enregistre une copie du texte à chaque fois qu'un rédacteur modifie un contenu. Si la base de données devient trop lourde, vous pouvez vider cet historique. Cela n'effacera <strong>aucun contenu public</strong>, cela supprimera uniquement la possibilité de revenir en arrière dans l'éditeur.
                    </p>

                    {if $total_revisions > 0}
                        {* 🟢 BOUTON QUI DÉCLENCHE LA MODALE *}
                        <button type="button" class="btn btn-danger btn-lg px-4" data-bs-toggle="modal" data-bs-target="#clearHistoryModal">
                            <i class="bi bi-trash-fill me-2"></i> Vider entièrement l'historique
                        </button>
                    {else}
                        <div class="alert alert-success d-inline-flex align-items-center mb-0">
                            <i class="bi bi-check-circle-fill me-2"></i> L'historique est propre et optimisé.
                        </div>
                    {/if}
                </div>
            </div>

        </div>
    </div>

    {* 🟢 MODALE BOOTSTRAP DE CONFIRMATION *}
    <div class="modal fade" id="clearHistoryModal" tabindex="-1" aria-labelledby="clearHistoryModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                {* Utilisation de MagixForms avec les classes validate_form et add_form *}
                <form action="index.php?controller=Revisions&action=clearAll" method="post" class="validate_form">
                    <input type="hidden" name="hashtoken" value="{$hashtoken}">

                    <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title" id="clearHistoryModalLabel">
                            <i class="bi bi-exclamation-triangle-fill me-2"></i> Confirmation requise
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body text-center py-4">
                        <p class="mb-0 fs-5">Êtes-vous sûr de vouloir supprimer <strong>définitivement</strong> les {$total_revisions} révisions ?</p>
                        <p class="text-muted mt-2 small">Cette action est irréversible.</p>
                    </div>

                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-danger">
                            <i class="bi bi-check-lg me-1"></i> Oui, tout supprimer
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
{/block}