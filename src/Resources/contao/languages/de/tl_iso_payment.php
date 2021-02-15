<?php

declare(strict_types=1);

/*
 * This file is part of the Wangaz\ContaoIsotopeWirecardBundle.
 *
 * (c) Wangaz
 * (c) inspiredminds
 *
 * @license CC-BY-SA-4.0
 */

$GLOBALS['TL_LANG']['tl_iso_payment'] = array_merge($GLOBALS['TL_LANG']['tl_iso_payment'], [
    'paypalSDKSandbox' => ['Testsystem', 'Im Testsystem (Sandbox) ausführen'],
    'paypalSDKNoSandbox' => ['Livesystem', 'Im Livesystem ausführen'],
    'paypalSDKSandboxOptions' => [
      'paypalSDKSandbox' => 'Testsystem',
      'paypalSDKNoSandbox' => 'Livesystem'
    ],
    'paypalSDKClientId' => ['Client ID', 'Ihre Client ID der Paypal API'],
    'paypalSDKSecret' => ['Secret', 'Ihr Secret der Paypal API'],
    'paypalSDKSBClientId' => ['Sandbox Client ID', 'Ihre Client ID der Paypal Sandbox API'],
    'paypalSDKSBSecret' => ['Sandbox Secret', 'Ihr Secret der Paypal Sandbox API'],
]);
