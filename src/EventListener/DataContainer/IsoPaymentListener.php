<?php

declare(strict_types=1);

/*
 * This file is part of the Kollmeier\ContaoIsotopePaypalSDKBundle.
 *
 * (c) Carsten Kollmeier
 *
 * @license CC-BY-SA-4.0
 */

namespace Kollmeier\ContaoIsotopePaypalSDKBundle\EventListener\DataContainer;

use Contao\CoreBundle\ServiceAnnotation\Callback;
use Contao\DataContainer;

class IsoPaymentListener {

  private $sandbox;

  public function onLoadCallback($value,DataContainer $dc) {

    if ('paypalSDKIsSandbox' === $dc->field) {
      return '0' === $value ? 'paypalSDKNoSandbox' : 'paypalSDKSandbox';
    }

  }

  public function onInputFieldCallback(DataContainer $dc) {
    if ('paypalSDKNoSandbox' === $dc->field) {
      return '';
    }
    return '';
  }

  public function onSaveCallback($value, $dc) {
    return 'paypalSDKNoSandbox' === $value ? '0' : '1';
  }

}
