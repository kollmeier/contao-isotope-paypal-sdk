<?php

declare(strict_types=1);

/*
 * This file is part of the Kollmeier\ContaoIsotopePaypalSDKBundle.
 *
 * (c) Carsten Kollmeier
 *
 * @license CC-BY-SA-4.0
 */


$GLOBALS['TL_LANG']['tl_iso_payment'] = array_merge($GLOBALS['TL_LANG']['tl_iso_payment'], [
    'paypalSDKClientId' => ['Client ID', 'Your Paypal API Client ID.'],
    'paypalSDKSecret' => ['Secret', 'Your Paypal API Secret.'],
    'paypalSDKSBClientId' => ['Sandbox Client ID', 'Your Paypal API Client ID.'],
    'paypalSDKSBSecret' => ['Sandbox Secret', 'Your Paypal API Secret.'],
]);
