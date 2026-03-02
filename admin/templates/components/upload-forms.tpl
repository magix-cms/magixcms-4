<form class="upload_form card p-4 shadow-sm border-0" action="/admin/index.php?controller=pages&action=uploadImages" method="POST" enctype="multipart/form-data">

    <div class="mb-3">
        <label for="images" class="form-label fw-bold">Ajouter des images</label>
        <input type="file" name="images[]" id="images" class="form-control" multiple required>
    </div>

    <button type="submit" class="btn btn-primary">
        <i class="bi bi-cloud-arrow-up me-2"></i> Envoyer
    </button>

    <div class="progress-container mt-4 d-none">
        <div class="d-flex justify-content-between mb-1 small text-muted">
            <span class="progress-status">Préparation...</span>
            <span class="progress-percentage fw-bold">0%</span>
        </div>
        <div class="progress" style="height: 12px;">
            <div class="progress-bar progress-bar-striped progress-bar-animated bg-primary"
                 role="progressbar"
                 style="width: 0%;"
                 aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">
            </div>
        </div>
    </div>

</form>

<div id="block-img" class="mt-4 row g-3"></div>