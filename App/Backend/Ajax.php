<?php

namespace BookneticAddon\Giftcards\Backend;

use BookneticApp\Models\Data;
use BookneticApp\Models\Appointment;
use BookneticAddon\Giftcards\Model\Giftcard;
use BookneticApp\Models\Location;
use BookneticApp\Models\Service;
use BookneticApp\Models\Staff;
use BookneticApp\Providers\Core\Capabilities;
use BookneticApp\Providers\Helpers\Helper;
use BookneticApp\Providers\UI\TabUI;
use BookneticApp\Backend\Appointments\Helpers\AppointmentSmartObject;
use function BookneticAddon\Giftcards\bkntc__;

class Ajax extends \BookneticApp\Providers\Core\Controller
{

    public function add_new()
	{
		$cid = Helper::_post('id', '0', 'integer');

		$services   = [];
        $staff      = [];
        $location   = [];

		if( $cid > 0 )
		{
			Capabilities::must( 'giftcards_edit' );

			$giftcardInf = Giftcard::get( $cid );

			foreach ( explode(',', $giftcardInf['services']) AS $serviceId )
			{
				if( $serviceId > 0 )
				{
					$serviceInf = Service::get( $serviceId );
					$services[] = [ $serviceId, $serviceInf['name'] ];
				}
			}

			foreach ( explode(',', $giftcardInf['staff']) AS $staffId )
			{
				if( $staffId > 0 )
				{
					$serviceInf = Staff::get( $staffId );
					$staff[] = [ $staffId, $serviceInf['name'] ];
				}
            }
            
			foreach ( explode(',', $giftcardInf['locations']) AS $locationId )
			{
				if( $locationId > 0 )
				{
					$locationInf = Location::get( $locationId );
					$location[] = [ $locationId, $locationInf['name'] ];
				}
			}
		}
		else
		{
			Capabilities::must( 'giftcards_add' );

			$giftcardInf = [
				'id'                =>  null,
				'code'              =>  null,
				'amount'            =>  null,
				'service'           =>  null,
				'location'          =>  null,
				'staff'             =>  null
			];
        }

        TabUI::get( 'giftcards_add_new' )
            ->item( 'details' )
            ->setTitle( bkntc__( 'Giftcard Details' ) )
            ->addView(__DIR__ . '/view/tab/giftcards_add_new_details.php')
            ->setPriority( 1 );

		return $this->modalView('add_new', [
			'giftcard'	=>	$giftcardInf,
			'services'	=>	$services,
            'staff'		=>	$staff,
            'locations' =>  $location
		]);
    }

	public function save_giftcard()
	{
		$id = Helper::_post('id', '0', 'integer');

		if( $id > 0 )
		{
			Capabilities::must( 'giftcards_edit' );
		}
		else
		{
			Capabilities::must( 'giftcards_add' );
		}

		$code				=	Helper::_post('code', '', 'string');
		$amount				=	Helper::_post('amount', '', 'float');
		$locations			=	Helper::_post('locations', '', 'string');
		$services			=	Helper::_post('services', '', 'string');
		$staff				=	Helper::_post('staff', '', 'string');

        $checkDuplicate = Giftcard::select(['code'])->where('code', $code)->where('id', '<>', $id)->fetch();

        if( $checkDuplicate )
        {
            return $this->response(false, bkntc__('The code already exists!'));
        }

		if( $amount <= 0)
		{
			return $this->response(false, bkntc__('Amount cannot be zero or negative number!') );
        }

        $locationArr = json_decode( $locations, true );
		$location = [];
		foreach ( $locationArr AS $locationId )
		{
			$location[] = (int)$locationId;
		}
		$locations = implode(',', $location);

		$servicesArr = json_decode( $services, true );
		$services = [];
		foreach ( $servicesArr AS $serviceId )
		{
			$services[] = (int)$serviceId;
		}
		$services = implode(',', $services);

		$staffArr = json_decode( $staff, true );
		$staff = [];
		foreach ( $staffArr AS $staffid )
		{
			$staff[] = (int)$staffid;
		}
		$staff = implode(',', $staff);

		if( empty($code) )
		{
			return $this->response(false, bkntc__('Please type the giftcard code field!'));
		}

		$sqlData = [
			'code'				=>	$code,
            'amount'			=>	$amount,
			'locations'			=>	$locations,
			'services'			=>	$services,
			'staff'				=>	$staff
		];

		if( $id > 0 )
		{
            $spent = Data::where('table_name', Appointment::getTableName() )
                ->where('data_key', 'giftcard_id')
                ->where('data_value', $id)
                ->sum('data_value');

			if ( $amount < $spent )
			{
				return $this->response(false, bkntc__('The gift card balance cannot be less than the amount spent!') );
			}

			Giftcard::where('id', $id)->update( $sqlData );
		}
		else
		{
			Giftcard::insert( $sqlData );
		}

		return $this->response(true );
	}

    public function get_services()
	{
		$search		= Helper::_post('q', '', 'string');

		$services = Service::where('name', 'LIKE', '%'.$search.'%')->fetchAll();
		$data = [];

		foreach ( $services AS $service )
		{
			$data[] = [
				'id'				=>	(int)$service['id'],
				'text'				=>	htmlspecialchars($service['name'])
			];
		}

		return $this->response(true, [ 'results' => $data ]);
	}

	public function get_staff()
	{
		$search	= Helper::_post('q', '', 'string');

		$staff  = Staff::where('name', 'LIKE', '%'.$search.'%')->fetchAll();
		$data   = [];

		foreach ( $staff AS $staffInf )
		{
			$data[] = [
				'id'				=>	(int)$staffInf['id'],
				'text'				=>	htmlspecialchars($staffInf['name'])
			];
		}

		return $this->response(true, [ 'results' => $data ]);
    }
    
    public function get_location()
	{
		$search	= Helper::_post('q', '', 'string');

		$location  = Location::where('name', 'LIKE', '%'.$search.'%')->fetchAll();
		$data   = [];

		foreach ( $location AS $locationInf )
		{
			$data[] = [
				'id'				=>	(int)$locationInf['id'],
				'text'				=>	htmlspecialchars($locationInf['name'])
			];
		}
		return $this->response(true, [ 'results' => $data ]);
	}

	public function giftcard_usage_history ()
	{
		Capabilities::must( 'giftcards_usage_history' );

		$giftcardId = Helper::_post('id', '0', 'integer');
		$data       = [];
		$giftInf    = [];
		$counter    = 0;

        $giftcardInf = Data::where('table_name', Appointment::getTableName() )
            ->where('data_key', 'giftcard_id')
            ->where('data_value', $giftcardId)
            ->fetchAll();

		foreach ( $giftcardInf as $gift )
		{
		    $appointmentInfo = AppointmentSmartObject::load( $gift->row_id );

            if ( ! $appointmentInfo->validate() )
            {
                continue;
            }

			$customerInfo               = $appointmentInfo->getCustomerInf();
			$serviceInfo                = $appointmentInfo->getServiceInf();

            $giftInf[ 'gift-' . $counter . '-customer_id' ] 	= $customerInfo->id;
            $giftInf[ 'gift-' . $counter . '-id' ] 		        = $gift->data_value;
			$giftInf[ 'gift-' . $counter . '-giftcard_amount' ] = Appointment::getData( $gift->row_id, 'giftcard_amount' );
			$giftInf[ 'gift-' . $counter . '-first_name' ] 		= $customerInfo->first_name;
			$giftInf[ 'gift-' . $counter . '-last_name' ] 		= $customerInfo->last_name;
			$giftInf[ 'gift-' . $counter . '-profile_image' ] 	= $customerInfo->profile_image;
			$giftInf[ 'gift-' . $counter . '-email' ] 			= $customerInfo->email;
			$giftInf[ 'gift-' . $counter . '-service_name' ]    = $serviceInfo->name;
			$giftInf[ 'gift-' . $counter . '-date' ]            = $appointmentInfo->getInfo()->date;
			$giftInf[ 'gift-' . $counter . '-appointment_id' ]  = $appointmentInfo->getInfo()->id;

			$data[] = $giftInf;

			$counter++;
		}

        TabUI::get( 'giftcards_usage_history' )
            ->item( 'details' )
            ->setTitle( bkntc__( 'Usage History Details' ) )
            ->addView(__DIR__ . '/view/tab/giftcards_usage_history_details.php')
            ->setPriority( 1 );


		return $this->modalView(  'giftcard_usage_history', [
			'giftcards'		=> $data
		] );
	}

}
