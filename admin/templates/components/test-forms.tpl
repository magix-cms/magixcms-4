<form class="validate_form add_form" action="/admin/index.php?controller=products&action=save">
    <div class="mb-3">
        <label for="name" class="form-label">Nom du produit</label>
        <input type="text" class="form-control" id="name" name="name" required minlength="3">
        <div class="invalid-feedback">Le nom est requis (3 caractères min).</div>
    </div>
    <button type="submit" class="btn btn-primary">Enregistrer</button>
</form>

<form class="validate_form" action="index.php?controller=categories&action=save" method="POST" enctype="multipart/form-data">
    <input type="file" name="icon">
    <button type="submit" class="btn btn-primary">Enregistrer</button>
</form>

<form class="upload_form" action="index.php?controller=products&action=uploadGallery" method="POST" enctype="multipart/form-data" data-edit-id="{$id}">
    <input type="file" name="gallery[]" multiple>

    <div class="progress-container d-none mt-3">
        <div class="progress"><div class="progress-bar"></div></div>
        <small class="progress-status"></small>
    </div>

    <button type="submit" class="btn btn-primary">Lancer l'envoi multiple</button>
</form>