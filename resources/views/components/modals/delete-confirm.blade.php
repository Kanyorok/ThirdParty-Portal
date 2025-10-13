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
                            onclick="if(this.form.checkValidity()){
                                    this.disabled = true;
                                    this.innerHTML = '<i class=&quot;fas fa-spinner fa-spin me-1&quot;></i> Please Wait...';
                                    this.form.submit();
                                }">
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
</script>
