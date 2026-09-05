<?php

declare(strict_types=1);
?>
<div
    class="modal fade"
    id="appConfirmModal"
    tabindex="-1"
    aria-labelledby="appConfirmTitle"
    aria-hidden="true"
>
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content app-confirm-modal">
            <div class="modal-header border-0 pb-0">
                <div class="app-confirm-icon" aria-hidden="true">?</div>
                <button
                    type="button"
                    class="btn-close"
                    data-bs-dismiss="modal"
                    aria-label="Close"
                ></button>
            </div>
            <div class="modal-body pt-2">
                <h2 id="appConfirmTitle" class="h4 mb-2">Please confirm</h2>
                <p id="appConfirmMessage" class="text-muted mb-0">
                    Are you sure you want to continue?
                </p>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button
                    type="button"
                    class="btn btn-outline-secondary"
                    data-bs-dismiss="modal"
                >
                    Cancel
                </button>
                <button
                    type="button"
                    class="btn btn-primary"
                    id="appConfirmAccept"
                >
                    Confirm
                </button>
            </div>
        </div>
    </div>
</div>
