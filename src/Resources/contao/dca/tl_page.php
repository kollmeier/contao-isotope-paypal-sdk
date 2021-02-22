<?php

use Kollmeier\ContaoIsotopePaypalSDKBundle\Isotope\Model\Payment\Paypal;
use Kollmeier\ContaoIsotopePaypalSDKBundle\Resources\contao\callbacks\PaymentCallback;

$GLOBALS['TL_DCA']['tl_page']['fields']['payment'] = [
    'exclude' => true,
    'inputType' => 'select',
    'options_callback' => [PaymentCallback::class,'getPayments'],
    'sql' => "varchar(255) default NULL",
];
$GLOBALS['TL_DCA']['tl_page']['fields']['success_page'] = [
    'exclude' => true,
    'inputType' => 'pageTree',
    'sql' => "int default NULL",
];
$GLOBALS['TL_DCA']['tl_page']['fields']['canceled_page'] = [
    'exclude' => true,
    'inputType' => 'pageTree',
    'sql' => "int default NULL",
];

// contao/dca/tl_page.php
$GLOBALS['TL_DCA']['tl_page']['palettes']['paypal_sdk_api'] =
    '{title_legend},title,alias,type;{paypal_sdk_api_legend},payment,success_page,canceled_page,{publish_legend},published,start,stop';
