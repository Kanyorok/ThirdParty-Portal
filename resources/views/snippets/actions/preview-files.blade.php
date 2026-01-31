<div class="modal fade" id="previewDocumentModal" tabindex="-1" role="dialog" aria-hidden="true" style="z-index: 10000 !important;">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">File Preview</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0">
                <div id="previewSpinner" class="text-center my-5 d-none">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
                <div class="embed-responsive embed-responsive-16by9" id="previewDocumentContent" style="min-height: 500px;">
                    <!-- Content loaded here -->
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Move modal to body to prevent z-index/clipping issues
        const modalEl = document.getElementById('previewDocumentModal');
        if (modalEl && modalEl.parentElement !== document.body) {
            document.body.appendChild(modalEl);
        }

        const previewModal = new bootstrap.Modal(modalEl);

        document.body.addEventListener('click', function (e) {
            const target = e.target.closest('.modal-preview-document');
            if (target) {
                e.preventDefault();
                
                // Set title
                const title = target.getAttribute('title') || 'Document';
                modalEl.querySelector('.modal-title').textContent = 'File: ' + title;

                // Show spinner, hide content
                document.getElementById('previewSpinner').classList.remove('d-none');
                document.getElementById('previewDocumentContent').innerHTML = '';
                
                // Show modal
                previewModal.show();

                const url = target.getAttribute('data-url');
                
                fetch(url)
                    .then(response => response.text())
                    .then(html => {
                        document.getElementById('previewSpinner').classList.add('d-none');
                        document.getElementById('previewDocumentContent').innerHTML = html;
                    })
                    .catch(error => {
                        console.error('Error loading preview:', error);
                        document.getElementById('previewSpinner').classList.add('d-none');
                        document.getElementById('previewDocumentContent').innerHTML = '<div class="alert alert-danger m-3">Failed to load document preview.</div>';
                    });
            }
        });
    });
</script>
