<?php
/*
 * Plugin Name: Giftcards for Booknetic
 * Description: You can create a giftcard with a corresponding amount and provide it to your customers.
 * Version: 1.1.3
 * Author: FS Code
 * Author URI: https://www.booknetic.com
 * License: Commercial
 * Text Domain: booknetic-giftcards
 */

defined( 'ABSPATH' ) or exit;

require_once __DIR__ . '/vendor/autoload.php';

add_filter('bkntc_addons_load', function ($addons)
{
    $addons[ \BookneticAddon\Giftcards\GiftcardAddon::getAddonSlug() ] = new \BookneticAddon\Giftcards\GiftcardAddon();
    return $addons;
});
