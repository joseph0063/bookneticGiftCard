<?php

namespace BookneticAddon\Giftcards;

use BookneticApp\Models\Appointment;
use BookneticApp\Providers\Helpers\Helper;
use BookneticApp\Backend\Appointments\Helpers\AppointmentRequests;
use BookneticApp\Providers\Common\PaymentGatewayService;

class GiftcardPaymentGateway extends PaymentGatewayService
{

	protected $slug = 'giftcard';

	public function __construct()
	{
		$this->setDefaultTitle( bkntc__('Giftcard') );
        $this->setDefaultIcon( GiftcardAddon::loadAsset('assets/frontend/icons/giftcard.png' ) );
	}

	public function when ( $status, $appointmentRequests = null )
	{
        if ( $status && Helper::getOption( 'hide_confirm_details_step', 'off' ) == 'on' )
        {
            return false;
        }

        return $status;
	}

    /**
     * @param AppointmentRequests $appointmentRequests
     * @return object
     */
    public function doPayment ( $appointmentRequests )
    {
        if ( ! empty( $appointmentRequests->giftcardInf ) )
        {
            foreach ( $appointmentRequests->appointments as $appointmentObj )
            {
                foreach ( $appointmentObj->createdAppointments as $appointmentId )
                {
                    if ( ( $appointmentObj->isRecurring() && $appointmentObj->serviceInf->recurring_payment_type == 'full' ) || $appointmentId === $appointmentObj->getFirstAppointmentId() )
                    {
                        Appointment::setData( $appointmentId, 'giftcard_id', $appointmentRequests->giftcardInf->id );
                        Appointment::setData( $appointmentId, 'giftcard_amount', $appointmentObj->getPayableToday() );
                    }
                }
            }

            self::confirmPayment( $appointmentRequests->paymentId );
        }

		return (object) [
			'status' => true,
			'data'   => []
		];
	}

}
