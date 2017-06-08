<?php
/**
 * The PayPal class definition.
 *
 * The payment processor to use with PayPal
 * @author Christopher Lee Reeves <ChrisReeves12@yahoo.com>
 **/

namespace Library\Plugin\PaymentMethod;

use Library\Model\Shop\IPayMethodStrategy;
use Library\Service\Settings;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Http\Headers;
use Zend\Http\Request;
use Zend\Http\Response;
use Zend\Http\Client as HTTPClient;

/**
 * Class PayPal
 * @package Library\Plugin\PaymentMethod
 */
class PayPal implements IPayMethodStrategy
{
    /**
     * @var string
     */
    protected $auth_endpoint = "https://api.paypal.com/v1/oauth2/token";

    /**
     * @var string
     */
    protected $sandbox_auth_endpoint = "https://api.sandbox.paypal.com/v1/oauth2/token";

    /**
     * @var string
     */
    protected $sandbox_endpoint = "https://api.sandbox.paypal.com/v1/payments/payment";

    /**
     * @var string
     */
    protected $endpoint = "https://api.paypal.com/v1/payments/payment";

    /**
     * @var string
     */
    protected $login_key;

    /**
     * @var string
     */
    protected $token;

    /**
     * @var bool
     */
    protected $sandbox;

    /**
     * @var ServiceLocatorInterface
     */
    protected $service_manager;

    /**
     * Processes the payment recieved
     *
     * @param array $info
     *
     * @return array
     * @throws \Exception
     */
    public function process($info = [])
    {
        // Get PayPal credentials
        $session_service = $this->service_manager->get('session');

        $this->login_key = Settings::get('paypal_client_id');
        $this->token = Settings::get('paypal_client_secret');
        $this->sandbox = Settings::get('paypal_sandbox_mode');
        $order_number = $info['order_number'];
        $subtotal = $item_discount = $store_credit = 0;
        $site_url = Settings::get('site_url');

        // Get correct endpoint if we are in sandbox mode
        if ($this->sandbox == 1)
        {
            $endpoint = $this->sandbox_endpoint;
            $auth_endpoint = $this->sandbox_auth_endpoint;
        } else
        {
            $endpoint = $this->endpoint;
            $auth_endpoint = $this->auth_endpoint;
        }

        // If transaction data has been set, the user is returning from PayPal
        if (isset($info['pay_info']['transaction_data']))
        {
            // Validate and finalize the transaction
            $trans_info = $this->_finalize($info);
        }
        else
        {
            // Get items
            $saved_cart = $saved_cart = $info['cart'];
            $shop_list_elements = $saved_cart->getShopListElements()->toArray();

            if (!empty($shop_list_elements))
            {
                // Build object to send to PayPal
                $data = new \stdClass();
                $data->intent = "authorize";
                $data->redirect_urls = new \stdClass();

                $data->redirect_urls->return_url = "https://{$site_url}/shopping-cart/return";
                $data->redirect_urls->cancel_url = "https://{$site_url}/shopping-cart";

                $data->payer = new \stdClass();
                $data->payer->payment_method = "PayPal";
                $data->transactions[0] = new \stdClass();
                $data->transactions[0]->invoice_number = $order_number;
                $data->transactions[0]->amount = new \stdClass();

                // Add shipping price and discount
                $data->transactions[0]->amount->details = new \stdClass();
                $data->transactions[0]->amount->details->shipping = number_format($saved_cart->getCurrentShippingCost(), 2);

                // Add tax
                $tax = $saved_cart->getTax();
                if ($saved_cart->getTax() > 0)
                {
                    $data->transactions[0]->amount->details->tax = number_format($tax, 2);
                } else
                {
                    $tax = 0;
                }

                // Get info on total
                $data->transactions[0]->amount->currency = "USD";
                $data->transactions[0]->description = Settings::get('store_name') . " Order";

                // Handle discounts
                if ($saved_cart->getDiscountAmount() > 0)
                {
                    $num_of_items = 0;
                    foreach ($shop_list_elements as $shop_list_element)
                    {
                        $num_of_items += $shop_list_element->getQuantity();
                    }


                    $item_discount = number_format($saved_cart->getDiscountAmount() / $num_of_items, 2, '.', '');
                }

                // Handle store credit vouchers
                if ($saved_cart->getStoreCredit() > 0)
                {
                    $num_of_items = 0;
                    foreach ($shop_list_elements as $shop_list_element)
                    {
                        $num_of_items += $shop_list_element->getQuantity();
                    }

                    $store_credit = number_format($saved_cart->getStoreCredit() / $num_of_items, 2, '.', '');
                }

                // Get products
                $idx = 0;
                $data->transactions[0]->item_list = new \stdClass();

                foreach ($shop_list_elements as $shop_list_element)
                {
                    $price = number_format($shop_list_element->getPrice() - $item_discount - $store_credit + $shop_list_element->getTax(), 2);
                    $data->transactions[0]->item_list->items[$idx] = new \stdClass();
                    $data->transactions[0]->item_list->items[$idx]->quantity = $shop_list_element->getQuantity();
                    $data->transactions[0]->item_list->items[$idx]->price = $price;
                    $data->transactions[0]->item_list->items[$idx]->currency = "USD";
                    $data->transactions[0]->item_list->items[$idx]->name = str_replace("\"", "'", $shop_list_element->getName());
                    $subtotal += $price * $shop_list_element->getQuantity();
                    $idx = $idx + 1;
                }

                // Calculate totals
                $total = $subtotal + $tax + number_format($saved_cart->getCurrentShippingCost(), 2);
                $data->transactions[0]->amount->total = number_format($total, 2);
                $data->transactions[0]->amount->details->subtotal = number_format($subtotal, 2);
            }

            if (!empty($data))
            {
                $data = stripslashes(json_encode($data));

                // Connect to remote server
                $http_client = new HTTPClient($auth_endpoint);
                $http_client->setOptions([
                    'adapter' => 'Zend\Http\Client\Adapter\Curl',
                    'maxredirects' => 0,
                    'sslverifypeer' => false,
                    'timeout' => 30
                ]);
                $http_client->setMethod(Request::METHOD_POST);
                $http_client->setParameterPost(['grant_type' => 'client_credentials']);
                $http_client->setAuth($this->login_key, $this->token);
                $response = $http_client->send();

                // Check authorization
                if (!($response instanceof Response))
                {
                    throw new \Exception("An error occured while trying to connect to PayPal");
                }

                // Check for errors in authenticating
                $auth_result = json_decode($response->getContent());
                if (isset($auth_result->error))
                {
                    throw new \Exception("Error authenticating with PayPal: " . $auth_result->error_description);
                }

                // Process payment with PayPal
                $http_headers = new Headers();
                $http_headers->addHeaderLine('Content-Type', 'application/json');
                $http_headers->addHeaderLine('Authorization', 'Bearer ' . $auth_result->access_token);
                $http_headers->addHeaderLine('Content-length', strlen($data));

                $http_client = new HTTPClient($endpoint);
                $http_client->setOptions([
                    'adapter' => 'Zend\Http\Client\Adapter\Curl',
                    'sslverifypeer' => false,
                    'maxredirects' => 0,
                    'timeout' => 30
                ]);
                $http_client->setMethod(Request::METHOD_POST);
                $http_client->setRawBody($data);
                $http_client->setHeaders($http_headers);
                $response = $http_client->send();

                // Check if results came back successfully
                if (!($response instanceof Response))
                {
                    throw new \Exception("No response was returned from PayPal when attempting to process this order, please contact site administrator.");
                }

                // Collect information from PayPal here
                $transaction_result = json_decode($response->getContent());
                if (!isset($transaction_result->id))
                {
                    throw new \Exception("Error processing PayPal Transaction: " . var_export($transaction_result, true));
                }

                $trans_info = ['success' => true, 'transaction_data' => $transaction_result, 'auth_result' => $auth_result, 'order_number' => $order_number, 'pay_method' => 'PayPal'];

                // Save this info to cart session
                $session_service->getContainer('cart')['pay_data'] = $trans_info;

                // Send user to PayPal
                exit(json_encode(['error' => false, 'redirect' => true, 'url' => $trans_info['transaction_data']->links[1]->href]));
            }
            else
            {
                // There is no data because no line items were provided
                exit(json_encode(['error' => true, 'message' => 'There are no products found on this order.']));
            }
        }

        return $trans_info;
    }

    /**
     * Finalizes incoming payments when users return from payment gateway
     *
     * @param $info
     *
     * @return array
     * @throws \Exception
     * @internal param $array
     */
    private function _finalize($info)
    {
        $user_service = $this->service_manager->get('user');
        $trans_info = [];

        // Get query strings
        $query = $_GET;

        if (isset($query['paymentId']) && isset($query['token']))
        {
            // Get links
            $execute_url = $info['pay_info']['transaction_data']->links[2]->href;
            $oauth_token = $info['pay_info']['auth_result']->access_token;

            // Finalize payment
            $http_headers = new Headers();
            $http_headers->addHeaderLine('Content-Type', 'application/json');
            $http_headers->addHeaderLine('Authorization', 'Bearer ' . $oauth_token);

            $http_client = new HTTPClient($execute_url);
            $http_client->setOptions([
                'adapter' => 'Zend\Http\Client\Adapter\Curl',
                'sslverifypeer' => false,
                'maxredirects' => 0,
                'timeout' => 30
            ]);
            $http_client->setHeaders($http_headers);
            $http_client->setMethod(Request::METHOD_POST);
            $http_client->setRawBody(json_encode(['payer_id' => $query['PayerID']]));
            $response = $http_client->send();

            // Parse response correctly
            if (!($response instanceof Response))
            {
                throw new \Exception("An error occured while finalizing PayPal request, an empty repsonse object was returned. Please contact administrator.");
            }
            else
            {
                $finalize_response_object = json_decode($response->getContent());
            }

            // If the transaction is successful, save order
            if (!isset($finalize_response_object->state) || $finalize_response_object->state != "approved")
            {
                throw new \Exception("An error occured while finalizing your PayPal request: " . $finalize_response_object->message);
            }

            // Build trans info to send
            $trans_info = [
                'success'       => true,
                'order_number'  => $info['order_number'],
                'response_text' => 'PayPal',
                'auth_code'     => 'PayPal',
                'trans_id'      => $info['pay_info']['transaction_data']->id,
                'card_num'      => 'N/A',
                'pay_type'      => 'PayPal',
                'tax'           => $info['cart']->getTax(),
                'grand_total'   => $info['cart']->getTotal(),
                'ip'            => $user_service->getClientIp()
            ];
        }

        return $trans_info;
    }

    /**
     * @param ServiceLocatorInterface $service_manager
     */
    public function setServiceManager(ServiceLocatorInterface $service_manager)
    {
        $this->service_manager = $service_manager;
    }
}