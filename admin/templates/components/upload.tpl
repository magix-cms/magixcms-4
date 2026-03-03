<form action="index.php?controller=Pages&action=uploadImages" method="post" enctype="multipart/form-data" class="magix-form-upload">
    <input type="hidden" name="id" value="{$page.id_pages}">

    <div class="mb-3">
        <label for="img_multiple" class="form-label">Ajouter des images à la galerie</label>
        <input type="file" name="img_multiple[]" id="img_multiple" class="form-control" multiple accept="image/*">
    </div>

    <div class="progress mb-3 d-none" id="upload-progress">
        <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: 0%"></div>
    </div>

    <button type="submit" class="btn btn-primary">
        <i class="bi bi-upload"></i> Envoyer les images
    </button>
</form>