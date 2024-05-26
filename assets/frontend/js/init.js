(function($)
{
    "use strict";

    $(document).ready(function()
    {

        bookneticHooks.addAction( 'loaded_step_confirm_details', function( booknetic )
        {
            let booking_panel_js = booknetic.panel_js;

            booking_panel_js.find( '.booknetic_payment_methods_footer' ).append(`
                <div class="booknetic_add_giftcard">
                    <input type="text" id="booknetic_giftcard" placeholder="${BookneticData.localization.giftcard}">
                    <button type="button" class="booknetic_btn_warning booknetic_giftcard_ok_btn">${BookneticData.localization.giftcard_add_btn}</button>
                </div>
            `);

            booking_panel_js.off('click', '.booknetic_giftcard_ok_btn' );
            booking_panel_js.on('click', '.booknetic_giftcard_ok_btn', function()
            {
                let ajaxParams = booknetic.ajaxParameters();

                ajaxParams.set( 'payment_method', 'giftcard' );

                booknetic.ajax('summary_with_giftcard', ajaxParams, function ( result )
                {
                    if( booking_panel_js.find('#booknetic_giftcard').val() === '' )
                    {
                        booking_panel_js.find('.booknetic_add_giftcard').removeClass('booknetic_giftcard_ok');
                        booking_panel_js.find('.booknetic_payment_methods').fadeIn(300);


                        booking_panel_js.find('.booknetic_prices_box').html( result['prices_html'] );

                        var paymentType = booking_panel_js.find('.booknetic_payment_method_selected').attr('data-payment-type');
                        booking_panel_js.find('.booknetic_payment_method_selected').data('payment-type', paymentType);
                    }
                    else
                    {
                        booking_panel_js.find(".booknetic_hide_on_local").removeClass('booknetic_hidden').fadeIn(100);
                        booking_panel_js.find('.booknetic_add_giftcard').addClass('booknetic_giftcard_ok');
                        booking_panel_js.find('.booknetic_prices_box').html( result['prices_html'] );

                        booking_panel_js.find('.booknetic_payment_method_selected').data('payment-type', 'giftcard');
                    }



                }, true, function ()
                {
                    booking_panel_js.find('.booknetic_add_giftcard').removeClass('booknetic_giftcard_ok');
                } );
            }).on( 'click', '[data-payment-type]', function () {
                let paymentMethod = $( this ).attr( 'data-payment-type' );

                if ( paymentMethod === 'giftcard' )
                {
                    booking_panel_js.find( '.booknetic_add_giftcard' ).css( 'display', 'flex' );
                }
                else
                {
                    booking_panel_js.find( '.booknetic_add_giftcard' ).css( 'display', 'none' );
                }
            } ).on( 'focusout click', '[data-payment-type]', function ()
            {
                if ( booking_panel_js.find( '#input_deposit_1' ).is(':checked') )
                    booking_panel_js.find( '.booknetic_deposit_price' ).hide();
            });
        });

        bookneticHooks.addAction( 'ajax_after_confirm_success', function( booknetic, data, result )
        {
            if( data.get('payment_method') == 'giftcard' )
            {
                booknetic.paymentFinished( true );
                booknetic.showFinishStep();
            }
        });

        bookneticHooks.addFilter('appointment_ajax_data', function ( data, booknetic ){

            let booking_panel_js = booknetic.panel_js;
            data.append( 'giftcard', booking_panel_js.find('#booknetic_giftcard').val() || '' );
            return data;

        });

    });
})(jQuery);