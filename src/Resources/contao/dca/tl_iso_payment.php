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
$GLOBALS['TL_DCA']['tl_iso_payment']['fields'] = array_merge($GLOBALS['TL_DCA']['tl_iso_payment']['fields'], [
    'paypalSDKSandbox' => [
        'label' => &$GLOBALS['TL_LANG']['tl_iso_payment']['paypalSDKSandbox'],
        'exclude' => true,
        'inputType' => 'checkbox',
        'load_callback' => static function($value,$dca) {
          dump($value);
          return $value ?: 'paypalSDKNoSandbox';
        },
        'save_callback' => static function($value,$dca) {
          dump($value);
          return $value === 'paypalSDKNoSandbox' ? '' : $value;
        },
        'eval' => [
          'submitOnChange' => true
        ],
        'sql' => "int(1) NOT NULL default 0",
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
$GLOBALS['TL_DCA']['tl_iso_payment']['palettes']['paypal_sdk'] = $GLOBALS['TL_DCA']['tl_iso_payment']['palettes']['cash'];
$GLOBALS['TL_DCA']['tl_iso_payment']['palettes']['__selector__'][] = 'paypalSDKSandbox';
$GLOBALS['TL_DCA']['tl_iso_payment']['palettes']['__selector__'][] = 'paypalSDKNoSandbox';
$GLOBALS['TL_DCA']['tl_iso_payment']['subpalettes']['paypalSDKNoSandbox'] = 'paypalSDKClientId,paypalSDKSecret';
$GLOBALS['TL_DCA']['tl_iso_payment']['subpalettes']['paypalSDKSandbox'] = 'paypalSDKSBClientId,paypalSDKSBSecret';

\Contao\CoreBundle\DataContainer\PaletteManipulator::create()
    ->addLegend('gateway_legend', 'price_legend', \Contao\CoreBundle\DataContainer\PaletteManipulator::POSITION_BEFORE)
    ->addField('paypalSDKSandbox','gateway_legend',\Contao\CoreBundle\DataContainer\PaletteManipulator::POSITION_APPEND)
    ->applyToPalette('paypal_sdk', 'tl_iso_payment')
;
