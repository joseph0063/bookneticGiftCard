<?php

defined('ABSPATH') or die();

use BookneticAddon\Giftcards\GiftcardAddon;
use BookneticApp\Providers\UI\TabUI;
use function BookneticAddon\Giftcards\bkntc__;

/***
 * @var mixed $parameters
 */

?>

<link rel="stylesheet" href="<?php echo GiftcardAddon::loadAsset('assets/backend/css/add_new.css' ) ?>">
<script type="text/javascript" src="<?php echo GiftcardAddon::loadAsset('assets/backend/js/add_new.js' ) ?>" id="add_new_JS" data-giftcard-id="<?php echo (int)$parameters['giftcard']['id'] ?>"></script>

<div class="fs-modal-title">
    <div class="title-icon badge-lg badge-purple"><i class="fa fa-plus"></i></div>
    <div class="title-text"><?php echo $parameters[ 'giftcard' ][ 'id' ] > 0 ? bkntc__( 'Edit Giftcard' ) : bkntc__('Add Giftcard') ?></div>
    <div class="close-btn" data-dismiss="modal"><i class="fa fa-times"></i></div>
</div>

<div class="fs-modal-body">
    <div class="fs-modal-body-inner">
        <form>

            <ul class="nav nav-tabs nav-light" data-tab-group="giftcards_add_new">
                <?php foreach ( TabUI::get( 'giftcards_add_new' )->getSubItems() as $tab ): ?>
                    <li class="nav-item"><a class="nav-link" data-tab="<?php echo $tab->getSlug(); ?>" href="#"><?php echo $tab->getTitle(); ?></a></li>
                <?php endforeach; ?>
            </ul>

            <div class="tab-content mt-5">
                <?php foreach ( TabUI::get( 'giftcards_add_new' )->getSubItems() as $tab ): ?>
                    <div class="tab-pane" data-tab-content="giftcards_add_new_<?php echo $tab->getSlug(); ?>" id="tab_<?php echo $tab->getSlug(); ?>"><?php echo $tab->getContent( $parameters ); ?></div>
                <?php endforeach; ?>
            </div>

        </form>
    </div>
</div>

<div class="fs-modal-footer">
    <button type="button" class="btn btn-lg btn-outline-secondary" data-dismiss="modal"><?php echo bkntc__('CANCEL') ?></button>
    <button type="button" class="btn btn-lg btn-primary" id="addGiftcardSave"><?php echo $parameters[ 'giftcard' ][ 'id' ] > 0 ? bkntc__( 'Save' ) :  bkntc__('ADD GIFTCARD') ?></button>
</div>