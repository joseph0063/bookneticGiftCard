<?php

defined( 'ABSPATH' ) or die();

/**
 * @var mixed $parameters
 */

use BookneticApp\Providers\Helpers\Helper;
use function BookneticAddon\Giftcards\bkntc__;

?>
<div class="form-row">
    <div class="form-group col-md-6">
        <label for="input_code"><?php echo bkntc__('Code') ?></label>
        <input type="text" class="form-control" id="input_code" value="<?php echo htmlspecialchars($parameters['giftcard']['code']) ?>">
    </div>
    <div class="form-group col-md-6">
        <label for="input_amount"><?php echo bkntc__('Amount (%s)', [ htmlspecialchars( Helper::currencySymbol() ) ] ) ?></label>
        <input type="text" class="form-control" id="input_amount" value="<?php echo htmlspecialchars($parameters['giftcard']['amount']) ?>">
    </div>
</div>

<div class="form-row">
    <div class="form-group col-md-12">
        <label for="input_locations"><?php echo bkntc__('Location filter') ?></label>
        <select class="form-control" id="input_locations" multiple>
            <?php
            foreach ($parameters['locations'] as $location)
            {
                echo '<option value="' . (int)$location[0] . '" selected>' . htmlspecialchars($location[1]) . '</option>';
            }
            ?>
        </select>
    </div>
</div>

<div class="form-row">
    <div class="form-group col-md-12">
        <label for="input_services"><?php echo bkntc__('Services filter') ?></label>
        <select class="form-control" id="input_services" multiple>
            <?php
            foreach ($parameters['services'] as $service) {
                echo '<option value="' . (int)$service[0] . '" selected>' . htmlspecialchars($service[1]) . '</option>';
            }
            ?>
        </select>
    </div>
</div>

<div class="form-row">
    <div class="form-group col-md-12">
        <label for="input_staff"><?php echo bkntc__('Staff filter') ?></label>
        <select class="form-control" id="input_staff" multiple>
            <?php
            foreach ($parameters['staff'] as $staff) {
                echo '<option value="' . (int)$staff[0] . '" selected>' . htmlspecialchars($staff[1]) . '</option>';
            }
            ?>
        </select>
    </div>
</div>
