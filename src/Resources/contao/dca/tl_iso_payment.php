<?php

declare(strict_types=1);

/*
 * This file is part of the Kollmeier\ContaoIsotopePaypalSDKBundle.
 *
 * (c) Carsten Kollmeier
 *
 * @license CC-BY-SA-4.0
 */


/*
 * Fields
 */

use Contao\CoreBundle\DataContainer\PaletteManipulator;
use Kollmeier\ContaoIsotopePaypalSDKBundle\Isotope\Model\Payment\Paypal;

$GLOBALS['TL_DCA']['tl_iso_payment']['fields'] = array_merge($GLOBALS['TL_DCA']['tl_iso_payment']['fields'], [
    'paypalSDKIsSandbox' => [
        'label' => &$GLOBALS['TL_LANG']['tl_iso_payment']['paypalSDKSandbox'],
        'exclude' => true,
        'inputType' => 'radio',
        'options' => ['paypalSDKNoSandbox', 'paypalSDKSandbox'],
        'reference' => &$GLOBALS['TL_LANG']['tl_iso_payment']['paypalSDKSandboxOptions'],
        'default' => 'paypalSDKNoSandbox',
        'eval' => [
          'submitOnChange' => true
        ],
        'sql' => "varchar(20) NOT NULL default 'paypalSDKNoSandbox'",
    ],
    'paypalSDKClientId' => [
        'label' => &$GLOBALS['TL_LANG']['tl_iso_payment']['paypalSDKClientId'],
        'exclude' => true,
        'inputType' => 'text',
        'eval' => ['mandatory' => true, 'maxlength' => 255, 'tl_class' => 'w50'],
        'sql' => "varchar(255) NOT NULL default ''",
    ],
    'paypalSDKSecret' => [
        'label' => &$GLOBALS['TL_LANG']['tl_iso_payment']['paypalSDKSecret'],
        'exclude' => true,
        'inputType' => 'text',
        'eval' => ['mandatory' => true, 'maxlength' => 255, 'tl_class' => 'w50'],
        'sql' => "varchar(255) NOT NULL default ''",
    ],
    'paypalSDKSBClientId' => [
        'label' => &$GLOBALS['TL_LANG']['tl_iso_payment']['paypalSDKSBClientId'],
        'exclude' => true,
        'inputType' => 'text',
        'eval' => ['mandatory' => true, 'maxlength' => 255, 'tl_class' => 'w50'],
        'sql' => "varchar(255) NOT NULL default ''",
    ],
    'paypalSDKSBSecret' => [
        'label' => &$GLOBALS['TL_LANG']['tl_iso_payment']['paypalSDKSBClientId'],
        'exclude' => true,
        'inputType' => 'text',
        'eval' => ['mandatory' => true, 'maxlength' => 255, 'tl_class' => 'w50'],
        'sql' => "varchar(255) NOT NULL default ''",
    ],
]);

/*
 * Palettes
 */
$GLOBALS['TL_DCA']['tl_iso_payment']['palettes'][Paypal::TYPE] = $GLOBALS['TL_DCA']['tl_iso_payment']['palettes']['cash'];
PaletteManipulator::create()
    ->addLegend('gateway_legend', 'price_legend', PaletteManipulator::POSITION_BEFORE)
    ->addField('paypalSDKIsSandbox','gateway_legend', PaletteManipulator::POSITION_APPEND)
    ->applyToPalette(Paypal::TYPE, 'tl_iso_payment')
;

$GLOBALS['TL_DCA']['tl_iso_payment']['palettes']['__selector__'][] = 'paypalSDKIsSandbox';
$GLOBALS['TL_DCA']['tl_iso_payment']['subpalettes']['paypalSDKIsSandbox_paypalSDKSandbox'] = 'paypalSDKSBClientId, paypalSDKSBSecret';
$GLOBALS['TL_DCA']['tl_iso_payment']['subpalettes']['paypalSDKIsSandbox_paypalSDKNoSandbox'] = 'paypalSDKClientId, paypalSDKSecret';
