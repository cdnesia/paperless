<!-- Delete Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" role="dialog" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h5 class="modal-title" id="deleteModalLabel">Konfirmasi Penghapusan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Apakah Anda yakin ingin menghapus <strong class="modal-item-name"></strong>?</p>
                <input type="hidden" class="modal-item-id" value="">
                <div class="modal-error alert alert-danger d-none mt-2 mb-0 py-2 small"></div>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">
                    <i class="fi fi-rr-cross me-1"></i> Batal
                </button>
                <button type="button" class="btn btn-danger btn-sm btn-confirm-hapus">
                    <i class="fi fi-rr-trash me-1"></i> Hapus
                </button>
            </div>
        </div>
    </div>
</div>
