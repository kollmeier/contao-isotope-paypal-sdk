<?php


namespace Kollmeier\ContaoIsotopePaypalSDKBundle\Service;

use Contao\Config;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\PageModel;
use Contao\System;
use Isotope\Isotope;
use Isotope\Model\ProductCollection\Order;
use Isotope\Module\Checkout;
use Kollmeier\ContaoIsotopePaypalSDKBundle\Isotope\Model\Payment\Paypal;
use Kollmeier\ContaoIsotopePaypalSDKBundle\Resources\contao\models\PaypalSDKApiPageModel;
use PayPalCheckoutSdk\Core\PayPalHttpClient;
use PayPalCheckoutSdk\Core\ProductionEnvironment;
use PayPalCheckoutSdk\Core\SandboxEnvironment;
use PayPalCheckoutSdk\Core\PayPalEnvironment;

class PaypalSDKService
{
    /** @var array|PayPalHttpClient[]  */
    private array $clients=[];

    /** @var array|PayPalEnvironment[] */
    private array $environments=[];

    /** @var array|Paypal[]  */
    private array $payments=[];

    private function getPaymentForName(string $name): ?Paypal {
        if (isset($this->payments[$name])) {
            return $this->payments[$name];
        }
        $paypal = Paypal::findOneByName($name);
        if ($paypal) {
            $this->payments[$name] = $paypal;
            return $this->payments[$name];
        }
        return null;
    }

    private function getEnvironmentForName(string $name): ?PayPalEnvironment {
        if (isset($this->environments[$name])) {
            return $this->environments[$name];
        }
        $paypal = $this->getPaymentForName($name);
        if ($paypal) {
            $sandbox = $paypal->paypalSDKIsSandbox === 'paypalSDKSandbox' || $paypal->debug;
            if ($sandbox) {
                $clientId = $paypal->paypalSDKSBClientId;
                $secret = $paypal->paypalSDKSBSecret;
                $this->environments[$name] = new SandboxEnvironment($clientId,$secret);
                return $this->environments[$name];
            }
            $clientId = $paypal->paypalSDKClientId;
            $secret = $paypal->paypalSDKSecret;
            $this->environments[$name] = new ProductionEnvironment($clientId,$secret);
            return $this->environments[$name];
        }
        return null;
    }

    public function getClientForName(string $name): ?PayPalHttpClient {
        if (!isset($this->clients[$name])) {
            $environment = $this->getEnvironmentForName($name);
            if ($environment) {
                $this->clients[$name] = new PayPalHttpClient($environment);
                return $this->clients[$name];
            }
            return null;
        }
        return $this->clients[$name];
    }

    public function buildRequestBody(Order $order): array {

        $successPage = \Environment::get('base') . Checkout::generateUrlForStep(Checkout::STEP_COMPLETE, $order);
        $cancelPage = \Environment::get('base') . Checkout::generateUrlForStep(Checkout::STEP_FAILED);


        $items = [];
        foreach ($order->getItems() as $item) {
            $row = [
                'name'  => strip_tags($item->name),
                'unit_amount' => [
                    'currency_code' => $order->getCurrency(),
                    'value' => number_format($item->getPrice(), 2)
                ],
                'quantity' => $item->quantity,
            ];

            if ($item->sku) {
                $row['sku'] = $item->sku;
            }

            $items[] = $row;
        }

        $breakdown = [
            'item_total' =>
                array(
                    'currency_code' => $order->getCurrency(),
                    'value' => number_format($order->getSubtotal(), 2)
                ),
        ];
        foreach ($order->getSurcharges() as $surcharge) {
            if (!$surcharge->addToTotal) {
                continue;
            }


            $breakdown[$surcharge->type] = [
                'currency_code' => $order->getCurrency(),
                'value' => number_format($surcharge->total_price, 2)
            ];
        }

        $billingAddress = $order->getBillingAddress();
        $shippingAddress = $order->getShippingAddress() ?? $billingAddress;

        $shop = Isotope::getConfig()->getLabel();

        $data = array(
            'intent' => 'CAPTURE',
            'application_context' =>
                array(
                    'user_action' => 'PAY_NOW',
                    'brand_name' => $shop,
                    'landingpage' => 'NO_PREFERENCE',
                    'shipping preference' => 'GET_FROM_FILE',
                    'return_url' =>  $successPage,
                    'cancel_url' => $cancelPage
                ),
            'purchase_units' =>
                array(
                    0 =>
                        array(
                            'reference_id' => $order->getUniqueId(),
                            'amount' =>
                                array(
                                    'currency_code' => $order->getCurrency(),
                                    'value' => number_format($order->getTotal(), 2),
                                    'breakdown' => $breakdown,
                                ),
                            'items' => $items,
                            'shipping' =>
                                array(
                                    'method' => $order->getShippingMethod()->getLabel(),
//                                    'address' =>
//                                        array(
//                                            'address_line_1'          => $shippingAddress->street_1,
//                                            'address_line_2'          => $shippingAddress->street_2,
//                                            'admin_area_2'           => $shippingAddress->city,
//                                            'admin_area_1'          => $shippingAddress->subdivision,
//                                            'postal_code'    => $shippingAddress->postal,
//                                            'country_code'   => strtoupper($shippingAddress->country),
//                                        ),
                                ),
                        )
                )
        );
        return $data;
    }
}