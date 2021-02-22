<?php

declare(strict_types=1);

/*
 * This file is part of the Kollmeier\ContaoIsotopePaypalSDKBundle.
 *
 * (c) Carsten Kollmeier
 *
 * @license CC-BY-SA-4.0
 */


namespace Kollmeier\ContaoIsotopePaypalSDKBundle\Isotope\Model\Payment;

use Contao\CoreBundle\Exception\RedirectResponseException;
use Contao\Module;
use Contao\StringUtil;
use Contao\System;
use Isotope\Interfaces\IsotopePayment;
use Isotope\Interfaces\IsotopeProductCollection;
use Isotope\Model\Payment;
use Isotope\Model\Payment\Postsale;
use Isotope\Model\ProductCollection\Order;
use Model;
use Model\Collection;
use Symfony\Component\HttpFoundation\Request;

class Paypal extends Payment
{

    public const TYPE='paypal_sdk';
    public const TPL_GROUP='iso_paypal_sdk';
    public const TPL_BUTTONS=self::TPL_GROUP.'_buttons';

    /**
     * List of types (classes) for this model
     * @var array
     */
    protected static $arrModelTypes = ['paypal_sdk'];


    public static function findAll(array $arrOptions=[]) {
        $arrOptions = array_merge($arrOptions,['column' => ['enabled=?', 'type IN (?)'], 'value' => [true,implode(',"',self::$arrModelTypes)]]);
        return self::find($arrOptions);
    }

    /**
     * @param string $name
     * @param bool|null $enabled
     * @param array|string[] $arrOptions
     * @return Paypal|Model|Collection|Paypal[]|null
     */
    public static function findByName(string $name, ?bool $enabled=true, array $arrOptions=[]) {
        $columns = ['name=?'];
        $values = [$name];
        if (null !== $enabled) {
            $columns[] = 'enabled=?';
            $values[] = $enabled;
        }
        return self::find(array_merge(['column' => $columns, 'value' => $values, 'return' => 'Collection'],$arrOptions));
    }

    /**
     * @param string $name
     * @param bool|null $enabled
     * @param array $arrOptions
     * @return Paypal|Model|null
     * @noinspection PhpMissingReturnTypeInspection
     */
    public static function findOneByName(string $name, ?bool $enabled=true, array $arrOptions=[])
    {
        return self::findByName($name,$enabled,array_merge($arrOptions,['return' => 'Model']));
    }

    public function processPayment(IsotopeProductCollection $objOrder, \Module $objModule)
    {
        // TODO: Implement processPayment() method.
    }

    /**
     * {@inheritdoc}
     */
    public function backendInterface($orderId)
    {
        if (($objOrder = Order::findByPk($orderId)) === null) {
            return parent::backendInterface($orderId);
        }

        $arrPayment = unserialize($objOrder->payment_data);

        if (!\is_array($arrPayment['PAYPAL_HISTORY']) || empty($arrPayment['PAYPAL_HISTORY'])) {
            return parent::backendInterface($orderId);
        }

        $strBuffer = '
<div id="tl_buttons">
<a href="' . ampersand(str_replace('&key=payment', '', \Environment::get('request'))) . '" class="header_back" title="' . specialchars($GLOBALS['TL_LANG']['MSC']['backBT']) . '">' . $GLOBALS['TL_LANG']['MSC']['backBT'] . '</a>
</div>';

        foreach ($arrPayment['PAYPAL_HISTORY'] as $response) {
            if ($response['intent'] === 'sale'
                && $response['state'] === 'approved'
                && isset($response['transactions'][0]['related_resources'][0]['sale']['id'])
            ) {
                $saleId = $response['transactions'][0]['related_resources'][0]['sale']['id'];

                $strBuffer .= '
<div class="maintenance_inactive">
<h2 class="sub_headline">' . $this->name . ' (' . $GLOBALS['TL_LANG']['MODEL']['tl_iso_payment'][$this->type][0] . ')' . '</h2>
<div class="tl_tbox">
<p><strong>' . sprintf($GLOBALS['TL_LANG']['MSC']['paypalTransaction'], $saleId) . '</strong></p>
<p>' . $GLOBALS['TL_LANG']['MSC']['paypalTransactionOnline'] .'</p>
<a class="tl_submit" href="https://www.paypal.com/activity/payment/' . $saleId . '" target="_blank">' . $GLOBALS['TL_LANG']['MSC']['paypalTransactionButton'] . '</a>
</div>
</div>';

                break;
            }
        }

        foreach (array_reverse($arrPayment['PAYPAL_HISTORY']) as $transaction) {
            if (isset($transaction['create_time'])) {
                $dateCreated = \Date::parse(
                    $GLOBALS['TL_CONFIG']['datimFormat'],
                    strtotime($transaction['create_time'])
                );
            } else {
                $dateCreated = '<i>UNKNOWN</i>';
            }

            $strBuffer .= '
<div class="maintenance_inactive">
<h2 class="sub_headline">' . sprintf($GLOBALS['TL_LANG']['MSC']['paypalTransactionDetails'], $dateCreated) . '</h2>
<table class="tl_show">
  <tbody>
';

            $render = function($k, $v, &$i) use (&$strBuffer) {
                $strBuffer .= '
  <tr>
    <td' . ($i % 2 ? '' : ' class="tl_bg"') . ' style="width:auto"><span class="tl_label">' . $k . ': </span></td>
    <td' . ($i % 2 ? '' : ' class="tl_bg"') . '>' . $v . '</td>
  </tr>';

                ++$i;
            };

            $loop = function($data, $loop, $i=0) use ($render, &$strBuffer) {
                if (null === $data) return;
                foreach ($data as $k => $v) {
                    if (\in_array($k, ['potential_payer_info', 'links', 'create_time'], true)) {
                        continue;
                    }
                    if (is_object($v)) {
                        $v = (array)$v;
                    }

                    if (\is_array($v)) {
                        $strBuffer .= '
  <tr>
    <td' . ($i % 2 ? '' : ' class="tl_bg"') . ' style="width:auto"><span class="tl_label">' . $k . ': </span></td>
    <td' . ($i % 2 ? '' : ' class="tl_bg"') . '>
      <table class="tl_show" style="border:1px solid #d0d0d2; background:#fff"><tbody>';

                        $i++;
                        $loop($v, $loop, (int) $i % 2);

                        $strBuffer .= '</td></tbody></table></tr>';

                        continue;
                    }

                    $render($k, $v, $i);
                }
            };

            $loop($transaction, $loop);

            $strBuffer .= '
</tbody></table>
</div>';
        }

        return $strBuffer;
    }


}
