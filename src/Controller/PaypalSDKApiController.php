<?php


namespace Kollmeier\ContaoIsotopePaypalSDKBundle\Controller;


use Kollmeier\ContaoIsotopePaypalSDKBundle\Service\PaypalSDKService;
use PayPalCheckoutSdk\Orders\OrdersCreateRequest;
use PayPalHttp\HttpResponse;
use Symfony\Component\Routing\Annotation\Route;

class PaypalSDKApiController
{
    private PaypalSDKService $paypalSDKService;

    public function __construct(PaypalSDKService $paypalSDKService)
    {
        $this->paypalSDKService = $paypalSDKService;
    }

    /**
     * @param string $name
     * @return HttpResponse
     * @Route("/paypalsdkapi/createorder/{name}/{moduleId}", name="paypal_sdk_api_createorder")
     */
    public function createOrder(string $name, int $moduleId): HttpResponse {
        $request = new OrdersCreateRequest();
        $request->prefer('return=representation');
        $request->body = $this->paypalSDKService->buildRequestBody($name, $moduleId);

        $client = $this->paypalSDKService->getClientForName($name);
        if ($client) {
            return $client->execute($request);
        }
        return new HttpResponse(404, '{"error": "No client found for '.$name.'"}', []);
    }
}
