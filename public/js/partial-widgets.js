// Reinitialize common widgets after partial loads
(function(){
	function initDataTables(){
		if(!window.jQuery || !jQuery.fn.DataTable) return;
		// Auto-init tables marked with data-datatable="auto" if not already
		jQuery('table[data-datatable="auto"]').each(function(){
			const $t = jQuery(this);
			if ( $.fn.dataTable.isDataTable(this) ) return;
			const opts = $t.data('dtOpts') || {};
			$t.DataTable(opts);
		});
	}

	function initSelect2(){
		if(!window.jQuery || !jQuery.fn.select2) return;
		jQuery('select[data-select2="auto"]').each(function(){
			const $s = jQuery(this);
			if($s.data('select2')) return;
			const placeholder = $s.attr('placeholder') || $s.data('placeholder') || '';
			$s.select2({width:'100%', placeholder});
		});
	}

	window.registerPartialInit(function(){
		initDataTables();
		initSelect2();
	});

	// First load
	document.addEventListener('DOMContentLoaded', function(){
		initDataTables();
		initSelect2();
	});
})();
