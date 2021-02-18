<?php

namespace Kollmeier\ContaoIsotopePaypalSDKBundle\EventListener;

use Contao\CoreBundle\ServiceAnnotation\Hook;
use Contao\Database;
use Contao\FrontendTemplate;
use Contao\Model\Collection;
use Contao\Template;
use Isotope\Isotope;
use Kollmeier\ContaoIsotopePaypalSDKBundle\Isotope\Model\Payment\Paypal;

/**
 * Class ReplaceInsertTagsListener
 * @package Kollmeier\ContaoIsotopePaypalSDKBundle\EventListener
 *
 * @Hook("replaceInsertTags")
 */
class ReplaceInsertTagsListener
{
    public function __invoke(
        string $insertTag,
        bool $useCache,
        string $cachedValue,
        array $flags,
        array $tags,
        array $arrCache,
        int $_rit,
        int $_cnt
    )
    {
        if (strpos($insertTag, "paypal_checkout_buttons") === 0) {
            list($tag,$p1,$p2) = explode("::", $insertTag . ' :: ::', 3);

            $p1 = strip_tags(trim($p1));
            $p2 = strip_tags(trim($p2));

            $templateName = Paypal::TPL_BUTTONS;
            $parameters = [];

            if ($p2) {
                $p = explode("::", $p2);
                foreach ($p as $item) {
                    $item = trim($item);
                    if ('' === $item) {
                        continue;
                    }
                    if (strpos($item,':') !== false) {
                        list($key,$value) = explode(':',$item,2);
                        $parameters[$key] = $value;
                    } else {
                        $parameters[] = $value;
                    }
                }

                if (isset($parameters['template_name'])) {
                    $templateName = Paypal::TPL_GROUP.'_'.$parameters['template_name'];
                }
            }

            if (!$p1) {
                return false;
            }

            $paypal = Paypal::findOneByName($p1);
            if (null === $paypal) {
                return '';
            }
            $template = new FrontendTemplate($templateName);

            $blnTesting = 'paypalSDKSandbox' === $paypal->paypalSDKIsSandbox || $paypal->debug;

            $template->paypal = $paypal;
            $template->name = $paypal->name;
            $template->clientId = $blnTesting ? $paypal->paypalSDKSBClientId : $paypal->paypalSDKClientId;
            $template->secret = $blnTesting ? $paypal->paypalSDKSBSecret : $paypal->paypalSDKSecret;
            $template->testing = $blnTesting;
            $template->currency = Isotope::getCart()->getCurrency();
            $template->amount = $parameters['amount'] ? preg_replace('/[^\d.]/','',str_replace(',','.',str_replace('.','',strip_tags(trim($parameters['amount']))))) : Isotope::getCart()->getTotal();
            $template->disableFunding = $parameters['disable-funding'] ?? null;

            return $template->parse();
        }
        return false;
    }
}