<?php

namespace BookneticAddon\Giftcards;


use BookneticApp\Backend\Settings\Helpers\LocalizationService;
use BookneticApp\Config;
use BookneticApp\Providers\Helpers\Helper;
use BookneticApp\Providers\UI\MenuUI;
use BookneticAddon\Giftcards\Backend\Ajax;
use BookneticAddon\Giftcards\Backend\Controller;
use BookneticApp\Providers\Core\AddonLoader;
use BookneticApp\Providers\Core\Capabilities;
use BookneticApp\Providers\Core\Route;
use BookneticApp\Providers\UI\TabUI;
use BookneticSaaS\Models\Tenant;

function bkntc__ ( $text, $params = [], $esc = true )
{
	return \bkntc__( $text, $params, $esc, GiftcardAddon::getAddonSlug() );
}

class GiftcardAddon extends AddonLoader
{

	public function init ()
	{
		Capabilities::registerTenantCapability( 'giftcards', bkntc__('Giftcards') );

		if( ! Capabilities::tenantCan( 'giftcards' ) )
			return;

		Capabilities::register( 'giftcards', bkntc__('Giftcards') );
		Capabilities::register( 'giftcards_add', bkntc__('Add new'), 'giftcards' );
		Capabilities::register( 'giftcards_edit', bkntc__('Edit'), 'giftcards' );
		Capabilities::register( 'giftcards_delete', bkntc__('Delete'), 'giftcards' );
		Capabilities::register( 'giftcards_usage_history', bkntc__('Usage history'), 'giftcards' );

        $this->registerShortCodes();
        Config::getShortCodeService()->addReplacer([ Listener::class, 'replace_short_code_text' ]);

        add_action( 'bkntc_appointment_requests_validate',      [ Listener::class, 'giftcard_validate' ], 999 );
        add_action( 'bkntc_appointment_requests_load',          [ Listener::class, 'giftcard_init' ], 999 );

		GiftcardPaymentGateway::load();
	}

	public function initBackend ()
	{
		if( ! Capabilities::tenantCan( 'giftcards' ) )
			return;

	    if( Capabilities::userCan('giftcards') )
	    {
            Route::get( 'giftcards', Controller::class );
            Route::post( 'giftcards', Ajax::class );

            MenuUI::get( 'giftcards' )
                ->setTitle( bkntc__( 'Giftcards' ) )
                ->setIcon('fa fa-gift')
                ->setPriority( 820 );


            TabUI::get('payment_gateways_settings')
                 ->item('giftcard')
                 ->setTitle( bkntc__( 'Giftcard' ) )
                 ->addView( __DIR__ . '/Backend/view/settings.php' );
        }

        add_filter('settings_booking_panel_labels_load' , function ($result){
            $result['Giftcard'] = bkntc__('Giftcard');
            return $result;
        });

        add_filter('bkntc_labels_settings_translates' , function ( $translates ){
            $translates['other_translates']['Giftcard'] = bkntc__('Giftcard');
            return $translates;
        });

        add_filter('bkntc_save_booking_labels_settings' , function (  $translates ,$language ){
            LocalizationService::saveFiles( $language, ['Giftcard' => $translates['Giftcard'] ] , GiftcardAddon::getAddonSlug() );
            unset($translates['Giftcard']);
            return $translates ;
        } , 10 , 2 );
	}

	public function initFrontend ()
	{
		if( ! Capabilities::tenantCan( 'giftcards' ) )
			return;

        add_action('bkntc_after_booking_panel_shortcode', function ()
        {
            wp_enqueue_script( 'booknetic-giftcards-init', static::loadAsset( 'assets/frontend/js/init.js' ), [ 'booknetic' ] );
        });

        $this->setFrontendAjaxController( Frontend\Ajax::class );

        add_filter( 'bkntc_add_files_through_ajax', [ self::class, 'addFilesThroughAjax' ] );
    }

    public function registerShortCodes()
    {
        Config::getShortCodeService()->registerShortCode( 'giftcard_code', [
            'name'      =>  bkntc__('Giftcard Code'),
            'category'  =>  'appointment_info',
            'depends'   =>  'appointment_id',
        ]);

        add_filter('bkntc_frontend_localization' , function ( $localization )
        {
            $localization['giftcard'] = bkntc__('Giftcard');
            $localization['giftcard_add_btn'] = bkntc__('ADD');
            return $localization;
        });
    }

    public function initSaaSBackend()
    {
		Tenant::onDeleting( [ Listener::class, 'beforeTenantDelete' ] );
    }

    public static function addFilesThroughAjax ( $result )
    {
        $result[ 'files' ][] = [
            'type' => 'js',
            'src'  => self::loadAsset( 'assets/frontend/js/init.js' ),
            'id'   => 'booknetic-giftcards-init',
        ];

        return $result;
    }
}
