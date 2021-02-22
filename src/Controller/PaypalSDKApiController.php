<?php


namespace Kollmeier\ContaoIsotopePaypalSDKBundle\Controller;

use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\CoreBundle\Routing\Page\DynamicRouteInterface;
use Contao\CoreBundle\Routing\Page\PageRoute;
use Contao\CoreBundle\ServiceAnnotation\Page;
use Contao\PageModel;
use Contao\StringUtil;
use Isotope\Isotope;
use Isotope\Model\Address;
use Isotope\Model\ProductCollection\Order;
use Kollmeier\ContaoIsotopePaypalSDKBundle\Resources\contao\models\PaypalSDKApiPageModel;
use Kollmeier\ContaoIsotopePaypalSDKBundle\Service\PaypalSDKService;
use PayPalCheckoutSdk\Orders\OrdersCreateRequest;
use PayPalCheckoutSdk\Orders\OrdersGetRequest;
use PayPalHttp\Serializer;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class PaypalSDKApiController
 * @package Kollmeier\ContaoIsotopePaypalSDKBundle\Controller
 * @Page(PaypalSDKApiPageModel::PAGE_TYPE, path="createorder", defaults={"_token_check": false }, urlSuffix=".json")
 * @Route("/{alias}/paypal_sdk_api/{method}", name=PaypalSDKApiPageModel::PAGE_TYPE, defaults={"_scope":"frontend","_token_check": false})
 */
class PaypalSDKApiController implements DynamicRouteInterface
{
    private PaypalSDKService $paypalSDKService;

    private ContaoFramework $framework;

    public function __construct(PaypalSDKService $paypalSDKService,
        ContaoFramework $framework
    )
    {
        $this->paypalSDKService = $paypalSDKService;
        $this->framework = $framework;
    }

    public function __invoke(string $alias, Request $request, string $method): Response {
        /** @var PaypalSDKApiPageModel $objPage */
        $objPage = PaypalSDKApiPageModel::findOneByAlias($alias);

        if (!$objPage) return JsonResponse::create([],404);

        /** @var PaypalSDKApiPageModel $objPage */

        $this->framework->initialize(true);

        $GLOBALS['objPage'] = $objPage;

        $objPage->loadDetails();

        if ('createorder' === $method) {
            return $this->createorder($objPage, $request);
        }
        if ('getorder' === $method) {
            return $this->getorder($objPage,$request->query->get('order_id'));
        }

        return JsonResponse::create(['status' => 200, 'payment' => $objPage->payment->name, 'method' => $method], 200);
    }

    public function createorder(PageModel $objPage, Request $request): Response {
        $cart = Isotope::getCart();

        if (!$cart) {
            return new JsonResponse([],404);
        }

        $order = $cart->getDraftOrder();

        if ($order && $order->checkout()) {

            $orderrequest = new OrdersCreateRequest();
            $orderrequest->prefer('return=representation');
            $orderrequest->body = $this->paypalSDKService->buildRequestBody($order);

            $client = $this->paypalSDKService->getClientForName($objPage->payment);
            if ($client) {
                $response = $client->execute($orderrequest);
                return new JsonResponse($response->result,$response->statusCode);
            }
        }
        return new JsonResponse( '{"error": "No client found for '.$objPage->payment.'"}', 404, []);
    }

    public function getorder(PageModel $objPage, string $orderId): Response {
        /** @var PaypalSDKApiPageModel $objPage */

        $client = $this->paypalSDKService->getClientForName($objPage->payment);
        if (!$client) {
            return JsonResponse::create([],404);
        }
        $response = $client->execute(new OrdersGetRequest($orderId));

        if ($response->result->purchase_units) {
            foreach ($response->result->purchase_units as $purchase) {
                if (isset($purchase->reference_id)) {
                    $order = Order::findOneBy('uniqId',(string)$purchase->reference_id);

                    if (!$order) continue;

                    $order->email_data = [$response->result->payer->email_address ?? ''];

                    if (isset($purchase->shipping->address)) {
                        $address = Address::createForProductCollection($order);
                        $objAddress = $purchase->shipping->address;
                        $address->setRow([
                            'firstname' => $response->result->payer->name->given_name ?? '',
                            'lastname' => $response->result->payer->name->surname ?? '',
                            'email' => $response->result->payer->email_address ?? '',
                            'street_1' => $objAddress->address_line_1 ?? '',
                            'street_2' => $objAddress->address_line_2 ?? '',
                            'city' => $objAddress->admin_area_2 ?? '',
                            'subdivision' => $objAddress->admin_area_1 ?? '',
                            'postal' => $objAddress->postal_code ?? '',
                            'country' => $objAddress->country_code ?? '',
                        ]);
                        $address->save();
                        $order->setBillingAddress($address);
                        $order->setShippingAddress($address);
                    }

                    $payment = $order->getPaymentMethod();

                    $paymentData = StringUtil::deserialize($order->payment_data, true);

                    if (!is_array($paymentData['PAYPAL_HISTORY'])) {
                        $paymentData['PAYPAL_HISTORY'] = [];
                    }

                    $paymentData['PAYPAL_HISTORY'][] = (array)$response->result;

                    $order->payment_data = $paymentData;

                    $order->checkout();
                    $order->setDatePaid(time());
                    $order->updateOrderStatus($payment->new_order_status);
                    $order->save();

                    if ($order->complete()) {
                        return JsonResponse::create($response->result,200);
                    }
                }
            }
        }

        return JsonResponse::create(['result' => 'error'], 400);
    }

    public function configurePageRoute(PageRoute $route): void
    {
//        dump($route);
    }

    public function getUrlSuffixes(): array
    {
        return ['.json','.html'];
    }


}
