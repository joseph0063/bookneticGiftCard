<?php

defined( 'ABSPATH' ) or die();

/**
 * @var mixed $parameters
 */

use BookneticApp\Providers\Helpers\Helper;
use BookneticApp\Providers\Helpers\Math;
use function BookneticAddon\Giftcards\bkntc__;
if( empty( $parameters['giftcards'] ) )
{
    echo bkntc__( 'Giftcard is not used yet!' );
}
else
{
?>
<div class="row mt-4">
    <div class="col-md-12">
        <div class="fs_data_table_wrapper">
            <table class="table-gray-2 dashed-border">
                <thead>
                <tr>
                    <th><?php echo bkntc__('CUSTOMER')?></th>
                    <th class="text-center"><?php echo bkntc__('USED AMOUNT')?></th>
                    <th class="text-center"><?php echo bkntc__('SERVICE')?></th>
                    <th class="text-center"><?php echo bkntc__('DATE')?></th>
                    <th class="text-center"><?php echo bkntc__('INFO')?></th>
                    <th></th>
                </tr>
                </thead>
                <tbody>
                <?php

                $counter = 0;
                foreach( $parameters['giftcards'] AS $giftcard )
                {
                    $paid = Math::floor( $giftcard['gift-'.$counter.'-giftcard_amount'] );

                    echo '<tr data-customer-id="' . (int)$giftcard['gift-'.$counter.'-customer_id'] . '" data-id="' . (int)$giftcard['gift-'.$counter.'-id'] . '">';
                    echo '<td>' . Helper::profileCard($giftcard['gift-'.$counter.'-first_name'], $giftcard['gift-'.$counter.'-profile_image'], $giftcard['gift-'.$counter.'-email'], 'Customers') . '</td>';
                    echo '<td class="text-center">' . Helper::price( $paid ) . '</td>';
                    echo '<td class="text-center">' . $giftcard['gift-'.$counter.'-service_name'] . '</td>';
                    echo '<td class="text-center">' . $giftcard['gift-'.$counter.'-date'] . '</td>';
                    echo '<td class="text-center" data-column="appointment_info" data-appointment-id="' . $giftcard['gift-'.$counter.'-appointment_id'] . '"><img class="invoice-icon" src="' . Helper::icon('invoice.svg') . '"></td>';
                    echo '</tr>';
                    $counter++;
                }
                ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php
}
?>