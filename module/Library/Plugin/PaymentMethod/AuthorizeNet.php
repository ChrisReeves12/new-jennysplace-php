<?php
/**
* The AuthorizeNet class definition.
*
* The payment processor to use with Authorize.net
* @author Christopher Lee Reeves <ChrisReeves12@yahoo.com>
**/

namespace Library\Plugin\PaymentMethod;

use Library\Model\Shop\IPayMethodStrategy;
use Library\Service\Settings;
use Zend\Http\Client as HTTPClient;
use Zend\Http\Request;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Class AuthorizeNet
 * @package Library\Plugin\PaymentMethod
 */
class AuthorizeNet implements IPayMethodStrategy
{
    /**
     * @var string
     */
    protected $endpoint = "https://secure.authorize.net/gateway/transact.dll";

    /**
     * @var string
     */
    protected $sandbox_endpoint = "https://test.authorize.net/gateway/transact.dll";

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
        // Get authorize.net credentials
        $user_service = $this->service_manager->get('user');
        $this->login_key = Settings::get('authorize_net_login');
        $this->token = Settings::get('authorize_net_tran_key');
        $this->sandbox = Settings::get('authorize_net_sandbox');
        $order_number = $info['order_number'];

        // Get correct endpoint if we are in sandbox mode
        if ($this->sandbox == 1)
        {
            $endpoint = $this->sandbox_endpoint;
        }
        else
        {
            $endpoint = $this->endpoint;
        }

        // Get user information
        $saved_cart = $info['cart'];
        $user = $info['cart']->getUser();
        $cart_items = $saved_cart->getShopListElements();

        // Create values to send
        $post_values = [
            "x_login"              => $this->login_key,
            "x_tran_key"           => $this->token,
            "x_version"            => "3.1",
            "x_delim_data"         => "TRUE",
            "x_delim_char"         => "|",
            "x_relay_response"     => "FALSE",
            "x_type"               => "AUTH_ONLY",
            "x_method"             => "CC",
            "x_po_num"             => $order_number,
            "x_invoice_num"        => $order_number,
            "x_card_num"           => str_replace(' ', '', $info['pay_info']['card_num']),
            "x_exp_date"           => $info['pay_info']['exp_month'] . $info['pay_info']['exp_year'],
            "x_card_code"          => $info['pay_info']['cvc'],
            "x_customer_ip"        => $user_service->getClientIp(),
            "x_cust_id"            => $user->getId(),
            "x_amount"             => $saved_cart->getTotal(),
            "x_description"        => "Order #: " . $order_number,
            "x_first_name"         => $user->getFirstName(),
            "x_last_name"          => $user->getLastName(),
            "x_company"            => $saved_cart->getBillingAddress()->getCompany(),
            "x_address"            => $saved_cart->getBillingAddress()->getLine1() . " " . $saved_cart->getBillingAddress()->getLine2(),
            "x_state"              => $saved_cart->getBillingAddress()->getState(),
            "x_city"               => $saved_cart->getBillingAddress()->getCity(),
            "x_zip"                => $saved_cart->getBillingAddress()->getZipcode(),
            "x_ship_to_first_name" => $saved_cart->getShippingAddress()->getFirstName(),
            "x_ship_to_last_name"  => $saved_cart->getShippingAddress()->getLastName(),
            "x_ship_to_company"    => $saved_cart->getShippingAddress()->getCompany(),
            "x_ship_to_address"    => $saved_cart->getShippingAddress()->getLine1() . " " . $saved_cart->getShippingAddress()->getLine2(),
            "x_ship_to_state"      => $saved_cart->getShippingAddress()->getState(),
            "x_ship_to_city"       => $saved_cart->getShippingAddress()->getCity(),
            "x_ship_to_zip"        => $saved_cart->getShippingAddress()->getZipcode(),
            "x_freight"            => "Order<|>" . $user->getFirstName() . ' ' . $user->getLastName() . "<|>" . $saved_cart->getCurrentShippingCost(),
            "x_currency_code"      => "USD",
            "x_email"              => $user->getEmail(),
            "x_email_customer"     => 'TRUE',
            "x_fax"                => $user->getFax(),
            "x_phone"              => $user->getPhone()
        ];

        // Add tax if applicable
        if ($saved_cart->getTax() > 0)
        {
            $post_values['x_tax'] = $saved_cart->getTax();
        }

        // Create a post string
        $post_string = "";
        foreach ($post_values as $key => $value)
        {
            $post_string .= "$key=" . urlencode($value) . "&";
        }

        $post_string = rtrim($post_string, "& ");

        // Create item list
        $line_items = [];
        if (count($cart_items) < 30)
        {
            foreach ($cart_items as $cart_item)
            {
                $item_name = $cart_item->getName();
                $product_id = $cart_item->getSku()->getProduct()->getProductCode();
                $qty = $cart_item->getQuantity();
                $price = $cart_item->getPrice();

                $line_items[] = "Itm#: $product_id<|>Itm#: $product_id<|>$item_name<|>$qty<|>$price<|>Y";
            }

            foreach ($line_items as $value)
            {
                $post_string .= "&x_line_item=" . urlencode($value);
            }
        }

        // Connect to remote server and try transaction
        $http_client = new HTTPClient($endpoint);
        $http_client->setOptions(['maxredirects' => 0, 'timeout' => 30]);
        $http_client->setMethod(Request::METHOD_POST);
        $http_client->setRawBody($post_string);
        $post_response = $http_client->send();

        // Check if connection was made successfully
        if ($post_response->getStatusCode() != 200)
        {
            throw new \Exception("An error occured while making connection to remote server: " . $post_response->getReasonPhrase());
        }

        $post_response = explode('|', $post_response->getBody());

        // Was this transaction successful?
        if ((int) $post_response[0] != 1)
        {
            $success = false;
        }
        else
        {
            $success = true;
        }

        $trans_info = [
            'success'       => $success,
            'response_text' => $post_response[3],
            'auth_code'     => $post_response[4],
            'trans_id'      => $post_response[6],
            'card_num'      => $post_response[50],
            'pay_type'      => $post_response[51],
            'tax'           => $saved_cart->getTax(),
            'grand_total'   => $saved_cart->getTotal(),
            'ip'            => $post_values['x_customer_ip']
        ];

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