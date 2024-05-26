<?php

namespace BookneticAddon\Giftcards;

use Exception;
use BookneticApp\Models\Data;
use BookneticApp\Models\Appointment;
use BookneticAddon\Giftcards\Model\Giftcard;
use BookneticApp\Backend\Appointments\Helpers\AppointmentRequests as Request;
use BookneticApp\Providers\Helpers\Helper;

class Listener
{
    public static function init ( $slug )
    {

    }

    /**
     * @throws Exception
     */
    public static function giftcard_validate ( Request $_ )
    {
		$giftcard =	Helper::_post('giftcard', '', 'str');

        if ( Request::self()->paymentMethod !== 'giftcard' ) return;

		if ( empty( $giftcard ) )
            throw new \Exception( bkntc__( 'Giftcard not found!' ) );

        $giftcardInf = Giftcard::where( 'code', $giftcard )->fetch();

		if ( ! $giftcardInf )
			throw new \Exception( bkntc__( 'Giftcard not found!' ) );

        $locationFilters = explode( ',', $giftcardInf->locations );
		$serviceFilters  = explode( ',', $giftcardInf->services );
		$staffFilters    = explode( ',', $giftcardInf->staff );

        foreach ( Request::appointments() as $appointmentObj )
        {
            if( ( !empty( $giftcardInf->locations ) && !in_array( (string)$appointmentObj->locationId, $locationFilters ) )
                || ( !empty( $giftcardInf->services ) && !in_array( (string)$appointmentObj->serviceId, $serviceFilters ) )
                || ( !empty( $giftcardInf->staff ) && !in_array( (string)$appointmentObj->staffId, $staffFilters ) ) )
            {
                throw new \Exception( bkntc__('Giftcard not found!') );
            }
        }

        $subQuery = Data::where( 'table_name', Appointment::getTableName() )
                        ->where( 'data_key', 'giftcard_id' )
                        ->where( 'data_value', $giftcardInf->id )
                        ->select( 'row_id' );

        $sum = Data::where( 'table_name', Appointment::getTableName() )
                   ->where( 'data_key', 'giftcard_amount' )
                   ->where( 'row_id', $subQuery )
                   ->sum( 'data_value' );

        $totalSpent = ! empty( $sum ) ? ( float ) $sum : 0;
        $balance    = $giftcardInf->amount - $totalSpent;

		if ( ( $balance - Request::self()->getPayableToday() ) < 0 )
			throw new \Exception( bkntc__( 'Giftcard balance is not enough!' ) );
    }

    public static function giftcard_init ()
    {
        $giftcard =	Helper::_post('giftcard', '', 'str');

        if ( empty( $giftcard ) ) return;

        $giftcardInf = Giftcard::where( 'code', $giftcard )->fetch();

        if ( ! $giftcardInf ) return;

        $subQuery = Data::where( 'table_name', Appointment::getTableName() )
                        ->where( 'data_key', 'giftcard_id' )
                        ->where( 'data_value', $giftcardInf->id )
                        ->select( 'row_id' );

        $sum = Data::where( 'table_name', Appointment::getTableName() )
                   ->where( 'data_key', 'giftcard_amount' )
                   ->where( 'row_id', $subQuery )
                   ->sum( 'data_value' );

        $totalSpent = ! empty( $sum ) ? ( float ) $sum : 0;
        $balance    = $giftcardInf->amount - $totalSpent;

        $request = Request::self();

        if ( ( $balance - $request->getPayableToday() ) < 0 ) return;

        $request->giftcardInf = $giftcardInf;
        $request->giftcardBalance = $balance;
    }

    public static function replace_short_code_text ( $text, $data )
    {
        if ( ! isset( $data[ 'appointment_id' ] ) ) return $text;

        $giftCardId = Appointment::getData( $data[ 'appointment_id' ], 'giftcard_id' );
        $giftCard   = Giftcard::get( $giftCardId );

        return str_replace('{giftcard_code}', $giftCard ? $giftCard->code : '', $text);
    }

    public static function beforeTenantDelete($tenantId)
    {
        Giftcard::noTenant()->where('tenant_id', $tenantId)->delete();
    }

}
