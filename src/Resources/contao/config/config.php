<?php

declare(strict_types=1);

/*
 * This file is part of the Kollmeier\ContaoIsotopePaypalSDKBundle.
 *
 * (c) Carsten Kollmeier
 *
 * @license CC-BY-SA-4.0
 */

\Isotope\Model\Payment::registerModelType('paypal_sdk', \Kollmeier\ContaoIsotopePaypalSDKBundle\Isotope\Model\Payment\Paypal::class);
