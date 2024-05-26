(function ($)
{
	"use strict";

	$(document).ready(function()
	{
		$('.fs-modal').on('click', '#addGiftcardSave', function ()
		{
			var code				= $("#input_code").val(),
				amount				= $("#input_amount").val(),
				locations			= $("#input_locations").val(),
				services			= $("#input_services").val(),
				staff				= $("#input_staff").val();

			var data = new FormData();

			data.append('id', $("#add_new_JS").data('giftcard-id'));
			data.append('code', code);
			data.append('amount', amount);
			data.append('locations', JSON.stringify( locations ));
			data.append('services', JSON.stringify( services ));
			data.append('staff', JSON.stringify( staff ));

			booknetic.ajax( 'save_giftcard', data, function()
			{
				booknetic.modalHide($(".fs-modal"));

				booknetic.dataTable.reload( $("#fs_data_table_div") );
			});
		})

		booknetic.select2Ajax( $(".fs-modal #input_services"), 'get_services');
		booknetic.select2Ajax( $(".fs-modal #input_staff"), 'get_staff');
		booknetic.select2Ajax( $(".fs-modal #input_locations"), 'get_location');

	});

})(jQuery);