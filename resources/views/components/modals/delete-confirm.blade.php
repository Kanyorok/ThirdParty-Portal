<!-- 🧨 Custom Delete Confirmation Modal -->
<div class="modal fade" id="customDeleteConfirmModal" tabindex="-1" aria-labelledby="deleteModalLabel"
     aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-3 shadow-sm">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="deleteModalLabel">Confirm Deletion</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete <strong id="deleteItemName">this item</strong>? This action cannot be
                    undone.</p>
            </div>
            <div class="modal-footer">
                <form id="customDeleteForm" method="POST" action="">
                    @csrf
                    @method('DELETE')
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    
                    <button type="submit" class="btn btn-danger"
                        onclick="handleDelete(this)">
                        Yes, Delete
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        const deleteButtons = document.querySelectorAll(".custom-delete-btn");
        const deleteForm = document.getElementById("customDeleteForm");
        const deleteItemName = document.getElementById("deleteItemName");

        deleteButtons.forEach(button => {
            button.addEventListener("click", function () {
                const itemName = this.getAttribute("data-name");
                const route = this.getAttribute("data-route");

                deleteItemName.textContent = itemName;
                deleteForm.setAttribute("action", route);
            });
        });
    });

    function handleDelete(button) {
        const form = button.closest('form');
        const submitBtn = button;

        // Disable button and show loading state
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Deleting...';

        // Create XMLHttpRequest for better error handling
        const xhr = new XMLHttpRequest();
        const formData = new FormData(form);

        xhr.open('POST', form.action, true);
        xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');

        xhr.onreadystatechange = function() {
            if (xhr.readyState === 4) {
                if (xhr.status === 200) {
                    try {
                        const response = JSON.parse(xhr.responseText);

                        // Close modal and redirect on success
                        const modal = bootstrap.Modal.getInstance(document.getElementById('customDeleteConfirmModal'));
                        modal.hide();

                        if (response.success) {
                            // Show success message
                            showToast('success', response.message || 'Item deleted successfully');
                            // Redirect to index page after short delay
                            setTimeout(() => {
                                window.location.href = '{{ route("invoiceentry.index") }}';
                            }, 1500);
                        } else {
                            // Show error message
                            showToast('error', response.message || 'Failed to delete item');
                            submitBtn.disabled = false;
                            submitBtn.innerHTML = 'Yes, Delete';
                        }
                    } catch (e) {
                        // If response is not JSON, treat as success and redirect
                        const modal = bootstrap.Modal.getInstance(document.getElementById('customDeleteConfirmModal'));
                        modal.hide();
                        showToast('success', 'Item deleted successfully');
                        setTimeout(() => {
                            window.location.href = '{{ route("invoiceentry.index") }}';
                        }, 1500);
                    }
                } else {
                    // Handle error response
                    try {
                        const response = JSON.parse(xhr.responseText);
                        showToast('error', response.message || 'Failed to delete item');
                    } catch (e) {
                        showToast('error', 'Failed to delete item. Please try again.');
                    }

                    // Re-enable button
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = 'Yes, Delete';
                }
            }
        };

        xhr.send(formData);

        return false; // Prevent default form submission
    }

    function showToast(type, message) {
        // Create a simple toast notification
        const toastHtml = `
            <div class="toast align-items-center text-white bg-${type === 'success' ? 'success' : 'danger'} border-0" role="alert" aria-live="assertive" aria-atomic="true" style="position: fixed; top: 20px; right: 20px; z-index: 9999;">
                <div class="d-flex">
                    <div class="toast-body">
                        ${message}
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
            </div>
        `;

        const toastContainer = document.createElement('div');
        toastContainer.innerHTML = toastHtml;
        document.body.appendChild(toastContainer);

        const toast = new bootstrap.Toast(toastContainer.querySelector('.toast'));
        toast.show();

        // Remove toast after it's hidden
        toastContainer.querySelector('.toast').addEventListener('hidden.bs.toast', () => {
            document.body.removeChild(toastContainer);
        });
    }
</script>
