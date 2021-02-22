<?php


namespace Kollmeier\ContaoIsotopePaypalSDKBundle\Resources\contao\models;


use Contao\Date;
use Contao\PageModel;

class PaypalSDKApiPageModel extends PageModel
{

    public const PAGE_TYPE="paypal_sdk_api";

    /**
     * @property string $payment
     * @property string $orderCompleteJumpTo
     * @property string $orderCanceledJumpTo
     */

    public static function findOneByAlias(string $alias, array $arrOptions = array()): ?PageModel
    {

        $t = static::$strTable;
        $arrColumns = array("$t.alias=?","$t.type='".self::PAGE_TYPE."'");

        if (!static::isPreviewMode($arrOptions)) {
            $time = Date::floorToMinute();
            $arrColumns[] = "$t.published='1' AND ($t.start='' OR $t.start<='$time') AND ($t.stop='' OR $t.stop>'$time')";
        }

        return static::findOneBy($arrColumns, $alias, $arrOptions);

    }

    public static function findOneByPayment(string $payment, array $arrOptions = array()): ?PageModel
    {
        $t = static::$strTable;
        $arrColumns = array("$t.payment=?","$t.type='".self::PAGE_TYPE."'");

        if (!static::isPreviewMode($arrOptions)) {
            $time = Date::floorToMinute();
            $arrColumns[] = "$t.published='1' AND ($t.start='' OR $t.start<='$time') AND ($t.stop='' OR $t.stop>'$time')";
        }

        return static::findOneBy($arrColumns, $payment, $arrOptions);

    }
}