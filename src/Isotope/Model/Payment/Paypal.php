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

    public const TPL_GROUP='iso_paypal_sdk';
    public const TPL_BUTTONS=self::TPL_GROUP.'_buttons';

    /**
     * List of types (classes) for this model
     * @var array
     */
    protected static $arrModelTypes = ['paypal_sdk'];

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

}
