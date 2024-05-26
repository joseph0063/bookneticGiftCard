<?php

namespace BookneticAddon\Giftcards\Frontend;

use BookneticApp\Models\Data;
use BookneticApp\Models\Appointment;
use BookneticAddon\Giftcards\Model\Giftcard;
use BookneticApp\Backend\Appointments\Helpers\AppointmentRequests;
use BookneticApp\Backend\Appointments\Helpers\AppointmentRequestData;
use BookneticApp\Providers\Core\FrontendAjax;
use BookneticApp\Providers\Helpers\Helper;

use function BookneticAddon\Giftcards\bkntc__;

class Ajax extends FrontendAjax
{

	public function summary_with_giftcard ()
	{
		try
		{
            $appointmentRequests    = AppointmentRequests::load();
            $appointmentObj         = $appointmentRequests->currentRequest();
		}
		catch ( \Exception $e )
		{
			return $this->response( false, $e->getMessage() );
		}

        $giftcard =	Helper::_post('giftcard', '', 'str');

        if ( empty( $giftcard ) )
        {
            return $this->response( false, bkntc__( 'Giftcard not found!' ) );
        }

        $giftcardInf = Giftcard::where( 'code', $giftcard )->fetch();

        if ( ! $giftcardInf )
        {
            return $this->response( false, bkntc__( 'Giftcard not found!' ) );
        }

        $locationsFilter = explode( ',', $giftcardInf->locations );
        $servicesFilter = explode( ',', $giftcardInf->services );
        $staffFilter    = explode( ',', $giftcardInf->staff );

        foreach ( $appointmentRequests->appointments as $appointmentObj )
        {
            if( ( !empty( $giftcardInf->locations ) && !in_array( (string)$appointmentObj->locationId, $locationsFilter ) )
                || ( !empty( $giftcardInf->services ) && !in_array( (string)$appointmentObj->serviceId, $servicesFilter ) )
                || ( !empty( $giftcardInf->staff ) && !in_array( (string)$appointmentObj->staffId, $staffFilter ) ) )
            {
                return $this->response( false, bkntc__('Giftcard not found!') );
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

        if ( ( $balance - $appointmentRequests->getPayableToday() ) < 0 )
        {
            return $this->response( false, bkntc__( 'Giftcard balance is not enough!' ) );
        }

		if ( ! empty( $appointmentRequests->giftcardInf ) )
		{
			$giftcardLine = $appointmentObj->price('giftcard_balance');
			$giftcardLine->setLabel( bkntc__('Giftcard balance') );
			$giftcardLine->setPrice(0);
			$giftcardLine->setPriceView( Helper::price( $appointmentRequests->giftcardBalance ) );
		}

		return $this->response( true, [
			'sum_price'             =>  $appointmentObj->getSubTotal(true ),
			'prices_html'           =>  $appointmentObj->getPricesHTML()
		] );
	}

}