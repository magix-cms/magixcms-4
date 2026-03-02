{extends file="layout.tpl"}

{block name='article'}
    <form class="validate_form add_form" action="index.php?controller={$controller}&action=save" method="post">

        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <h1 class="h3 mb-1 fw-bold">Modifier le produit</h1>
                <span class="badge bg-primary-subtle text-primary">ID: #{$data.id_product}</span>
            </div>
            <div class="d-flex gap-2">
                <a href="index.php?controller={$controller}" class="btn btn-outline-secondary">Annuler</a>
                <button type="submit" class="btn btn-primary px-4">Enregistrer</button>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-12 col-lg-8">
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-body">
                        <h5 class="card-title mb-4">Informations générales</h5>

                        <div class="form-floating mb-3">
                            <input type="text" class="form-control" id="product_name" name="name" placeholder="Nom du produit" value="{$data.name}" required>
                            <label for="product_name">Nom du produit</label>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Description</label>
                            <textarea class="form-control" name="description" rows="10">{$data.description}</textarea>
                            <div class="form-text">Utilisez le Markdown ou l'éditeur riche.</div>
                        </div>
                    </div>
                </div>

                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0 fw-bold">Galerie Photos</h5>
                    </div>
                    <div class="card-body">
                        {* Ici on pourra mettre notre .upload_form avec progressbar plus tard *}
                        <div class="block-img row g-2">
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-lg-4">

                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-body">
                        <h5 class="card-title mb-3">Disponibilité</h5>
                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" role="switch" id="active" name="active" {if $data.active}checked{/if}>
                            <label class="form-check-label" for="active">Produit activé</label>
                        </div>

                        <label class="form-label fw-bold">Catégorie principale</label>
                        <select class="form-select mb-3" name="id_category">
                            <option value="1">Électronique</option>
                            <option value="2">Vêtements</option>
                        </select>
                    </div>
                </div>

                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        <h5 class="card-title mb-3">Logistique</h5>
                        <label class="form-label">Prix de vente</label>
                        <div class="input-group mb-3">
                            <input type="number" step="0.01" class="form-control" name="price" value="{$data.price}">
                            <span class="input-group-text">€</span>
                        </div>

                        <label class="form-label">Quantité en stock</label>
                        <input type="number" class="form-control" name="stock" value="{$data.stock}">
                    </div>
                </div>
            </div>
        </div>
    </form>
{/block}