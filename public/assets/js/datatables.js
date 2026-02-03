/* eslint-disable */
/**
 * DataTables Initialization
 * Global configuration and helper functions for DataTables
 */

(function ($) {
    'use strict';

    // Default DataTables configuration
    $.extend(true, $.fn.dataTable.defaults, {
        responsive: true,
        autoWidth: false,
        pageLength: 25,
        lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
        language: {
            search: "_INPUT_",
            searchPlaceholder: "Search...",
            lengthMenu: "Show _MENU_ entries",
            info: "Showing _START_ to _END_ of _TOTAL_ entries",
            infoEmpty: "No entries available",
            infoFiltered: "(filtered from _MAX_ total entries)",
            zeroRecords: "No matching records found",
            emptyTable: "No data available in table",
            paginate: {
                first: "First",
                last: "Last",
                next: "Next",
                previous: "Previous"
            }
        },
        dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>' +
             '<"row"<"col-sm-12"tr>>' +
             '<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>'
    });

    // Initialize DataTables on page load
    $(document).ready(function () {
        // Auto-initialize tables with .datatable class
        if ($.fn.DataTable) {
            $('.datatable').each(function () {
                if (!$.fn.DataTable.isDataTable(this)) {
                    $(this).DataTable();
                }
            });
        }
    });

    // Helper function to reinitialize DataTables
    window.initDataTable = function (selector, options) {
        var $table = $(selector);
        if ($.fn.DataTable.isDataTable($table)) {
            $table.DataTable().destroy();
        }
        return $table.DataTable(options || {});
    };

})(jQuery);
