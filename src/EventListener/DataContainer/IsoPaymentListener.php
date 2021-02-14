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

  public function onLoadCallback($value, DataContainer $dc);
    dump($value,$dc);
  }

  public function onFieldCallback(DataContainer $dc, $label) {
    dump($dc,$label);
  }

}
