<?php

namespace BookneticAddon\Giftcards\Backend;

use BookneticApp\Providers\UI\DataTableUI;
use BookneticApp\Providers\Core\Capabilities;
use BookneticApp\Providers\DB\DB;
use BookneticApp\Providers\Helpers\Helper;
use BookneticAddon\Giftcards\Model\Giftcard;
use function BookneticAddon\Giftcards\bkntc__;


class Controller extends \BookneticApp\Providers\Core\Controller
{

    public function index()
    {
		$dataTable = new DataTableUI( new Giftcard() );

        $dataTable->addAction('edit', bkntc__('Edit'));

        if (Capabilities::userCan('giftcards_delete'))
        {
            $dataTable->addAction('delete', bkntc__('Delete'), function ($ids)
            {
                Giftcard::where('id', $ids)->delete();
            }, DataTableUI::ACTION_FLAG_BULK_SINGLE);
        }

		$dataTable->setTitle(bkntc__('Giftcards'));
		$dataTable->addNewBtn(bkntc__('ADD GIFTCARD'));

		$dataTable->searchBy(["code"]);

		$dataTable->addColumns(bkntc__('№'), DataTableUI::ROW_INDEX);
		$dataTable->addColumns(bkntc__('CODE'), 'code');

		$dataTable->addColumns(bkntc__('BALANCE'), function( $gift )
		{
			return Helper::price( $gift[ 'amount' ] );
		}, ['attr' => ['column' => 'amount']]);

        $spent = 0;

		$dataTable->addColumns(bkntc__('SPENT'), function( $gift ) use ( &$spent )
		{
            $spent = DB::DB()->get_row( DB::DB()->prepare( 'SELECT SUM( `d2`.`data_value` ) AS `summary` FROM `' . DB::table( 'data' ) . '` AS `d1` LEFT JOIN `' . DB::table( 'data' ) . '` AS `d2` ON `d1`.`row_id` = `d2`.`row_id` AND `d2`.`data_key` = "giftcard_amount" WHERE `d1`.`data_key` = "giftcard_id" AND `d1`.`data_value` = %d', $gift[ 'id' ] ) );
            $spent = empty( $spent->summary ) ? 0 : ( float ) $spent->summary;

			return Helper::price( $spent );
		}, ['attr' => ['column' => 'spent']]);

		$dataTable->addColumns(bkntc__('LEFTOVER'), function( $gift ) use ( &$spent )
		{
			$leftover   = $gift[ 'amount' ] - $spent;

			return Helper::price( $leftover );
		}, ['attr' => ['column' => 'leftover']]);

		$dataTable->addColumns(bkntc__('USAGE HISTORY'), function()
		{
			return '<img class="invoice-icon" src="' . Helper::icon('invoice.svg') . '">';
		}, ['attr' => ['column' => 'usage_history'], 'is_html' => true,]);

        $table = $dataTable->renderHTML();

        $this->view( 'index', ['table' => $table]);
    }

}