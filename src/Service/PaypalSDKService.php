<?php


namespace Kollmeier\ContaoIsotopePaypalSDKBundle\Service;

use Contao\PageModel;
use Isotope\Isotope;
use Isotope\Module\Cart;
use Isotope\Module\Checkout;
use Kollmeier\ContaoIsotopePaypalSDKBundle\Isotope\Model\Payment\Paypal;
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
            $sandbox = $paypal->paypalSDKIsSandbox === 'paypalSKDSandbox' || $paypal->debug;
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

    public function buildRequestBody(string $name, int $moduleId): array {
        $paypal = $this->getPaymentForName($name);
        if (!$paypal) {
            return [];
        }
        $cart =Isotope::getCart();
        if (!$cart) {
            return [];
        }
        $checkout = new Checkout(\ModuleModel::findById($moduleId));
        if (!$checkout) {
            return [];
        }
        $successPage = PageModel::findById($checkout->orderCompleteJumpTo);
        if (!$successPage) {
            return [];
        }
        return array(
            'intent' => 'CAPTURE',
            'application_context' =>
                array(
                    'return_url' =>  $successPage->getAbsoluteUrl(),
                    'cancel_url' => $successPage->getAbsoluteUrl('canceled=1')
                ),
            'purchase_units' =>
                array(
                    0 =>
                        array(
                            'amount' =>
                                array(
                                    'currency_code' => $cart->getCurrency(),
                                    'value' => $cart->getTotal()
                                )
                        )
                )
        );
    }
}