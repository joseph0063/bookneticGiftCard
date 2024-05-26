<?php

defined( 'ABSPATH' ) or die();

use BookneticApp\Providers\Helpers\Helper;
use BookneticAddon\Giftcards\GiftcardAddon;
use function BookneticAddon\Giftcards\bkntc__;

/***
 * @var mixed $parameters
 */

echo $parameters['table'];
?>
<link rel="stylesheet" href="<?php echo GiftcardAddon::loadAsset('assets/backend/css/info.css' )?>">
<script type="text/javascript" src="<?php echo GiftcardAddon::loadAsset('assets/backend/js/giftcard.js' )?>"></script>
<script type="text/javascript" src="<?php echo GiftcardAddon::loadAsset('assets/backend/js/info.js' )?>"></script>
