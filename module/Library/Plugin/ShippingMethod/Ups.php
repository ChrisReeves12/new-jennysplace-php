<?php
/**
* The Ups class definition.
*
* Gets shipping rates from UPS
* @author Christopher Lee Reeves <ChrisReeves12@yahoo.com>
**/

namespace Library\Plugin\ShippingMethod;

use Library\Model\Shop\IShippingMethodStrategy;
use Library\Model\Shop\ShopList\Cart;
use Library\Model\User\Address;
use Library\Service\DB\EntityManagerSingleton;
use Library\Service\Settings;

/**
 * Class Ups
 * @package Library\Plugin\ShippingMethod
 */
class Ups implements IShippingMethodStrategy
{
    /**
     * @param Cart $cart
     *
     * @return array|void
     * @throws \Exception
     */
    public function get_methods(Cart $cart)
    {
        // Get entity manager
        $em = EntityManagerSingleton::getInstance();
        $results = [];

        // Load correct settings
        $store_info = Settings::getAll();
        $userid = $store_info['ups_login_name'];
        $passwd = $store_info['ups_password'];
        $access = $store_info['ups_access_key'];
        $carrier = "UPS";
        $error = false;

        $zf_dir = getcwd();
        $wsdl = $zf_dir . "/public/ups_wsdl/RateWS.wsdl";

        $operation = "ProcessRate";
        $endpointurl = 'https://onlinetools.ups.com/webservices/Rate';

        $weight = $cart->getTotalWeight();
        if ($weight < 0.1)
        {
            $weight = 0.1;
        }

        // Shipment info
        $shipper_adr_line_1 = $store_info['store_address_line_1'];
        $shipper_city = $store_info['store_address_city'];
        $shipper_zip = $store_info['store_address_zipcode'];
        $shipper_state = $store_info['store_address_state'];

        // Address to object
        $shiptoaddress = $cart->getShippingAddress();

        if ($shiptoaddress instanceof Address)
        {
            $to_adr_line_1 = $shiptoaddress->getLine1();
            $to_city = $shiptoaddress->getCity();
            $to_state = $shiptoaddress->getState();
            $to_zip = $shiptoaddress->getZipcode();

            // Create soap request
            $option['RequestOption'] = 'Shop';
            $request['Request'] = $option;

            $pickuptype['Code'] = '01';
            $pickuptype['Description'] = 'Daily Pickup';
            $request['PickupType'] = $pickuptype;

            $customerclassification['Code'] = '01';
            $customerclassification['Description'] = 'Classfication';
            $request['CustomerClassification'] = $customerclassification;

            $address['AddressLine'] = $shipper_adr_line_1;
            $address['City'] = $shipper_city;
            $address['StateProvinceCode'] = $shipper_state;
            $address['PostalCode'] = $shipper_zip;
            $address['CountryCode'] = 'US';
            $shipper['Address'] = $address;
            $shipment['Shipper'] = $shipper;

            $addressTo['AddressLine'] = $to_adr_line_1;
            $addressTo['City'] = $to_city;
            $addressTo['StateProvinceCode'] = $to_state;
            $addressTo['PostalCode'] = $to_zip;
            $addressTo['CountryCode'] = 'US';
            $addressTo['ResidentialAddressIndicator'] = '';
            $shipto['Address'] = $addressTo;
            $shipment['ShipTo'] = $shipto;

            $addressFrom['AddressLine'] = $shipper_adr_line_1;
            $addressFrom['City'] = $shipper_city;
            $addressFrom['StateProvinceCode'] = $shipper_state;
            $addressFrom['PostalCode'] = $shipper_zip;
            $addressFrom['CountryCode'] = 'US';
            $shipfrom['Address'] = $addressFrom;
            $shipment['ShipFrom'] = $shipfrom;

            $service['Code'] = '03';
            $service['Description'] = 'Service Code';
            $shipment['Service'] = $service;

            $packaging1['Code'] = '02';
            $packaging1['Description'] = 'Rate';
            $package1['PackagingType'] = $packaging1;
            $dunit1['Code'] = 'IN';
            $dunit1['Description'] = 'inches';
            $dimensions1['Length'] = '15';
            $dimensions1['Width'] = '15';
            $dimensions1['Height'] = '15';
            $dimensions1['UnitOfMeasurement'] = $dunit1;
            $package1['Dimensions'] = $dimensions1;
            $punit1['Code'] = 'LBS';
            $punit1['Description'] = 'Pounds';
            $packageweight1['Weight'] = $weight;
            $packageweight1['UnitOfMeasurement'] = $punit1;
            $package1['PackageWeight'] = $packageweight1;

            $shipment['Package'] = [$package1];
            $shipment['ShipmentServiceOptions'] = '';
            $shipment['LargePackageIndicator'] = '';
            $request['Shipment'] = $shipment;
        }

        $mode = [
            'trace'        => 1
        ];

        // Initialize soap client
        $client = new \SoapClient($wsdl, $mode);

        // Set endpoint url
        $client->__setLocation($endpointurl);

        // Create soap header
        $usernameToken['Username'] = $userid;
        $usernameToken['Password'] = $passwd;
        $serviceAccessLicense['AccessLicenseNumber'] = $access;
        $upss['UsernameToken'] = $usernameToken;
        $upss['ServiceAccessToken'] = $serviceAccessLicense;

        $header = new \SoapHeader('http://www.ups.com/XMLSchema/XOLTWS/UPSS/v1.0', 'UPSSecurity', $upss);
        $client->__setSoapHeaders($header);

        // Get response
        $resp = false;

        if (!empty($request))
        {
            try
            {
                $resp = $client->__soapCall($operation, [$request]);
            }
            catch (\Exception $e)
            {
                // If fault occurs, simply disable shipping prices
                if ($e->getCode() == 0)
                {
                    $error = true;
                } else
                {
                    throw $e;
                }
            }
        }

        // Get shipping methods from database
        $shipping_methods = $em->getRepository('Library\Model\Shop\ShippingMethod')->findBy(['carrier' => $carrier, 'inactive' => false]);
        $shipping_methods_map = [];
        foreach ($shipping_methods as $shipping_method)
        {
            $shipping_methods_map[$shipping_method->getCarrierId()] = $shipping_method->getName();
        }

        if (!$error || empty($results))
        {
            foreach ($resp->RatedShipment as $info)
            {
                // Check if shipping method is valid to be used
                if (!isset($shipping_methods_map[(int)$info->Service->Code]))
                    continue;

                // Print shipping options
                $results[] = [
                    'shipping_method_id' => (int)$info->Service->Code,
                    'name'               => $shipping_methods_map[(int) $info->Service->Code],
                    'price'              => $info->TotalCharges->MonetaryValue,
                    'carrier'            => $carrier
                ];
            }
        }
        else
        {
            // If an error occured, perhaps the order is too heavy to get a quote
            $results[] = [
                'shipping_method_id' => 3,
                'name'               => 'Standard Shipping (Shipping price will be quoted after processing due to order size)',
                'price'              => '0.00',
                'carrier'            => $carrier
            ];
        }

        return $results;
    }
}