/**
 * Generic Bulk Action Handler
 * Fixes issues with bulk actions when only one item is selected
 */
class BulkActionHandler {
  constructor(options = {}) {
    this.selectAllCheckbox = options.selectAllCheckbox || '#selectAll';
    this.itemCheckboxes = options.itemCheckboxes || 'input[name="selectedItems[]"]';
    this.bulkActionButtons = options.bulkActionButtons || '.bulk-action-btn';
    this.form = options.form || 'form[data-bulk-action]';
    this.minSelections = options.minSelections || 1;

    this.init();
  }

  init() {
    this.bindEvents();
    this.updateButtonStates();
  }

  bindEvents() {
    // Handle select all checkbox
    $(this.selectAllCheckbox).on('change', (e) => {
      const isChecked = e.target.checked;
      $(this.itemCheckboxes).prop('checked', isChecked);
      this.updateButtonStates();
    });

    // Handle individual checkboxes
    $(document).on('change', this.itemCheckboxes, () => {
      this.updateSelectAllState();
      this.updateButtonStates();
    });

    // Handle bulk action button clicks
    $(this.bulkActionButtons).on('click', (e) => {
      e.preventDefault();
      this.handleBulkAction(e.target);
    });
  }

  updateSelectAllState() {
    const totalCheckboxes = $(this.itemCheckboxes).length;
    const checkedCheckboxes = $(`${this.itemCheckboxes}:checked`).length;

    const selectAllCheckbox = $(this.selectAllCheckbox);

    if (checkedCheckboxes === 0) {
      selectAllCheckbox.prop('checked', false);
      selectAllCheckbox.prop('indeterminate', false);
    } else if (checkedCheckboxes === totalCheckboxes) {
      selectAllCheckbox.prop('checked', true);
      selectAllCheckbox.prop('indeterminate', false);
    } else {
      selectAllCheckbox.prop('checked', false);
      selectAllCheckbox.prop('indeterminate', true);
    }
  }

  updateButtonStates() {
    const selectedCount = $(`${this.itemCheckboxes}:checked`).length;
    const hasMinimumSelections = selectedCount >= this.minSelections;

    $(this.bulkActionButtons).each((index, button) => {
      const $button = $(button);
      // const action = $button.data('action');

      if (hasMinimumSelections) {
        $button.prop('disabled', false);
        $button.removeClass('disabled');

        // Update button text to show count
        const originalText = $button.data('original-text') || $button.text();
        if (!originalText.includes('(')) {
          $button.data('original-text', originalText);
        }

        if (selectedCount > 0) {
          $button.text(`${originalText} (${selectedCount})`);
        }
      } else {
        $button.prop('disabled', true);
        $button.addClass('disabled');

        // Restore original text
        const originalText = $button.data('original-text');
        if (originalText) {
          $button.text(originalText);
        }
      }
    });
  }

  handleBulkAction(button) {
    const $button = $(button);
    const action = $button.data('action');
    const selectedItems = $(`${this.itemCheckboxes}:checked`).map(function () {
      return $(this).val();
    }).get();

    if (selectedItems.length === 0) {
      this.showAlert('Please select at least one item to perform this action.', 'warning');
      return;
    }

    if (selectedItems.length < this.minSelections) {
      this.showAlert(`Please select at least ${this.minSelections} item(s) to perform this action.`, 'warning');
      return;
    }

    // Confirm action
    const actionName = $button.text().replace(/\s*\(\d+\)/, ''); // Remove count from text
    const confirmed = confirm(`Are you sure you want to ${actionName.toLowerCase()} ${selectedItems.length} selected item(s)?`);

    if (!confirmed) {
      return;
    }

    // Show loading state
    this.setLoadingState($button, true);

    // Submit form or make AJAX request
    this.submitBulkAction(action, selectedItems, $button);
  }

  submitBulkAction(action, selectedItems, $button) {
    const form = $(this.form);

    if (form.length > 0) {
      // Add selected items to form
      form.find('input[name="selectedItems[]"]').remove();
      selectedItems.forEach((itemId) => {
        form.append(`<input type="hidden" name="selectedItems[]" value="${itemId}">`);
      });

      // Add action to form
      form.find('input[name="action"]').remove();
      form.append(`<input type="hidden" name="action" value="${action}">`);

      // Submit form
      form.submit();
    } else {
      // Make AJAX request
      $.ajax({
        url: $button.data('url') || window.location.href,
        method: 'POST',
        data: {
          action,
          selectedItems,
          _token: $('meta[name="csrf-token"]').attr('content'),
        },
        success: (response) => {
          this.showAlert(response.message || 'Action completed successfully', 'success');
          this.refreshTable();
        },
        error: (xhr) => {
          const errorMessage = xhr.responseJSON?.message || 'An error occurred while processing the request';
          this.showAlert(errorMessage, 'error');
        },
        complete: () => {
          this.setLoadingState($button, false);
        },
      });
    }
  }

  setLoadingState($button, isLoading) {
    if (isLoading) {
      $button.prop('disabled', true);
      $button.data('original-text', $button.text());
      $button.html('<i class="fas fa-spinner fa-spin"></i> Processing...');
    } else {
      $button.prop('disabled', false);
      const originalText = $button.data('original-text');
      if (originalText) {
        $button.text(originalText);
      }
    }
  }

  showAlert(message, type = 'info') {
    // Use existing notification system or create a simple alert
    if (typeof toastr !== 'undefined') {
      toastr[type](message);
    } else if (typeof Swal !== 'undefined') {
      Swal.fire({
        icon: type,
        title: type.charAt(0).toUpperCase() + type.slice(1),
        text: message,
        timer: 3000,
      });
    } else {
      alert(message);
    }
  }

  refreshTable() {
    // Refresh DataTable if it exists
    if (typeof $.fn.DataTable !== 'undefined') {
      $('.dataTable').each(function () {
        if ($.fn.DataTable.isDataTable(this)) {
          $(this).DataTable().ajax.reload();
        }
      });
    }

    // Or reload the page
    // window.location.reload();
  }
}

// Initialize when document is ready
$(document).ready(() => {
  // Initialize bulk action handler
  // eslint-disable-next-line no-new
  new BulkActionHandler({
    selectAllCheckbox: '#selectAll',
    itemCheckboxes: 'input[name="selectedItems[]"]',
    bulkActionButtons: '.bulk-action-btn',
    form: 'form[data-bulk-action]',
    minSelections: 1,
  });
});
