<?php


namespace Kollmeier\ContaoIsotopePaypalSDKBundle\Resources\contao\callbacks;


use Kollmeier\ContaoIsotopePaypalSDKBundle\Isotope\Model\Payment\Paypal;

class PaymentCallback
{
    public static function getPayments(): array {
        $payments = Paypal::findAll();
        $return =[];
        if ($payments) {
            foreach ($payments->getModels() as $payment) {
                $return[$payment->name] = $payment->label;
            }
        }
        return $return;
    }
}