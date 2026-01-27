// Reinitialize common widgets after partial loads
(function () {
  function initDataTables() {
    if (!window.jQuery || !jQuery.fn.DataTable) return;
    // Auto-init tables marked with data-datatable="auto" if not already
    jQuery('table[data-datatable="auto"]').each(function () {
      const $t = jQuery(this);
      if ($.fn.dataTable.isDataTable(this)) return;
      const opts = $t.data('dtOpts') || {};
      // Defensive: if table body contains a single placeholder row with a colspan equal or greater
      // than the number of header columns, DataTables will mis-map columns and raise TN/4.
      const $tbody = $t.find('tbody');
      let headerCols = $t.find('thead th').length || 0;
      if ($tbody.find('tr').length === 1) {
        const $firstTd = $tbody.find('tr:first td');
        if ($firstTd.length === 1) {
          const colspan = parseInt($firstTd.attr('colspan') || '0', 10);
          // If headerCols is zero for some reason, fallback to colspan
          const effectiveCols = headerCols > 0 ? headerCols : colspan;
          if (colspan >= effectiveCols && effectiveCols > 0) {
            // Replace placeholder single-cell row with an empty row containing the same
            // number of cells as headers so DataTables will map columns correctly.
            let emptyRow = '<tr>';
            for (let i = 0; i < effectiveCols; i += 1) {
              emptyRow += '<td></td>';
            }
            emptyRow += '</tr>';
            $tbody.html(emptyRow);
            // ensure headerCols reflects effectiveCols for later logic
            headerCols = effectiveCols;
            // continue to initialize DataTable below
          }
        }
      }

      $t.DataTable(opts);
    });
  }

  function initSelect2() {
    if (!window.jQuery || !jQuery.fn.select2) return;
    jQuery('select[data-select2="auto"]').each(function () {
      const $s = jQuery(this);
      if ($s.data('select2')) return;
      const placeholder = $s.attr('placeholder') || $s.data('placeholder') || '';
      $s.select2({ width: '100%', placeholder });
    });
  }

  window.registerPartialInit(() => {
    initDataTables();
    initSelect2();
  });

  // First load
  document.addEventListener('DOMContentLoaded', () => {
    initDataTables();
    initSelect2();
  });
}());
