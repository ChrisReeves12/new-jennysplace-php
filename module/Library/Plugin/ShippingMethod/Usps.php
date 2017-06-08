<?php
/**
* The Usps class definition.
*
* Gets shipping methods from the United States Postal Service
* @author Christopher Lee Reeves <ChrisReeves12@yahoo.com>
**/

namespace Library\Plugin\ShippingMethod;

use Library\Model\Shop\IShippingMethodStrategy;
use Library\Model\Shop\ShopList\Cart;
use Library\Service\Settings;
use Zend\Http\Request;
use Zend\Http\Response;
use Zend\Http\Client as HTTPClient;

/**
 * Class Usps
 * @package Library\Plugin\ShippingMethod
 */
class Usps implements IShippingMethodStrategy
{
    /**
     * @param Cart $cart
     *
     * @return array|void
     * @throws \Exception
     */
    public function get_methods(Cart $cart)
    {
        // Load correct settings
        $store_info = Settings::getAll();
        $userid = $store_info['usps_user_id'];

        $endpointurl = "http://production.shippingapis.com/ShippingAPI.dll";

        $request_data = new \SimpleXMLElement('<RateV4Request></RateV4Request>');
        $request_data->addChild('Revision', 2);
        $request_data->addAttribute('USERID', $userid);
        $package_xml = $request_data->addChild('Package');
        $package_xml->addAttribute('ID', '1st');
        $package_xml->addChild('Service', 'ALL');
        $package_xml->addChild('ZipOrigination', $store_info['store_address_zipcode']);
        $package_xml->addChild('ZipDestination', $cart->getShippingAddress()->getZipcode());
        $package_xml->addChild('Pounds', $cart->getTotalWeight());

        $package_xml->addChild('Ounces', 0);
        $package_xml->addChild('Container', null);
        $package_xml->addChild('Size', 'REGULAR');
        $package_xml->addChild('Width', '15');
        $package_xml->addChild('Length', '15');
        $package_xml->addChild('Height', '15');
        $package_xml->addChild('Girth', '15');
        $package_xml->addChild('Value', $cart->getSubTotal());
        $package_xml->addChild('Machinable', 'False');

        $request_data_xml = $request_data->asXML();
        $post_fields = [
            'API' => 'RateV4',
            'XML' => $request_data_xml
        ];

        // Send request to remote server
        $http_client = new HTTPClient($endpointurl);
        $http_client->setOptions([
            'adapter' => 'Zend\Http\Client\Adapter\Curl',
            'maxredirects' => 0,
            'sslverifypeer' => false,
            'timeout' => 30
        ]);
        $http_client->setMethod(Request::METHOD_POST);
        $http_client->setParameterPost($post_fields);
        $response = $http_client->send();

        // Check response object
        if (!($response instanceof Response))
        {
            throw new \Exception("The USPS API return an empty response object, please contact administrator.");
        }

        // Parse up response
        $response_xml = new \SimpleXMLElement($response->getContent());
        $shipping_info = [];

        foreach ($response_xml->Package->Postage as $element)
        {
            $shipping_info[] = [
                'shipping_method_id' => (string) $element['CLASSID'],
                'price' => (string) $element->Rate,
                'name' => (string) $element->MailService,
                'carrier' => 'USPS'
            ];
        }

        return $shipping_info;
    }
}