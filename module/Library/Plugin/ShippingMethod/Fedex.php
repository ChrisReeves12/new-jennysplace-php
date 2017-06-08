<?php
/**
* The Fedex class definition.
*
* The plugin used to get Fedex rates
* @author Christopher Lee Reeves <ChrisReeves12@yahoo.com>
**/

namespace Library\Plugin\ShippingMethod;

require_once __DIR__ . "/Fedex/fedex-common.php";

use Library\Model\Shop\IShippingMethodStrategy;
use Library\Model\Shop\ShopList\Cart;
use Library\Service\Settings;
use SoapClient;
use SoapFault;

/**
 * Class Fedex
 * @package Library\Plugin\ShippingMethod
 */
class Fedex implements IShippingMethodStrategy
{
    /**
     * @param Cart $cart
     *
     * @return array|void
     * @throws SoapFault
     * @throws \Exception
     */
    public function get_methods(Cart $cart)
    {
        // Get entity manager
        $store_info = Settings::getAll();
        $key = $store_info['fedex_key'];
        $password = $store_info['fedex_password'];
        $account_number = $store_info['fedex_account_number'];
        $meter_number = $store_info['fedex_meter_number'];

        $zf_dir = getcwd();
        $wsdl = $zf_dir . "/public/fedex_wsdl/RateService_v14.wsdl";
        $shipping_info = [];

        ini_set("soap.wsdl_cache_enabled", "0");
        $client = new SoapClient($wsdl, ['trace' => 1]);

        $request['WebAuthenticationDetail'] = [
            'UserCredential' => [
                'Key' => $key,
                'Password' => $password
            ]
        ];

        $request['Version'] = [
            'ServiceId' => 'crs',
            'Major' => '14',
            'Intermediate' => '0',
            'Minor' => '0'
        ];

        $request['ClientDetail'] = [
            'AccountNumber' => $account_number,
            'MeterNumber' => $meter_number
        ];

        $request['TransactionDetail'] = ['CustomerTransactionId' => 'Fedex Shipping'];

        $request['ReturnTransitAndCommit'] = true;
        $request['RequestedShipment'] =
        [
            'DropoffType' => 'REGULAR_PICKUP',
            'ShipTimestamp' => date('c'),
            'PackagingType' => 'YOUR_PACKAGING',
            'RateRequestTypes' => [ 'ACCOUNT', 'LIST' ],
            'PackageCount' => 1,
            'Shipper' =>
                [
                    'Contact' =>
                        [
                            'PersonName' => null,
                            'PhoneNumber' => null
                        ],
                    'Address' =>
                        [
                            'StreetLines' =>
                                [
                                    $store_info['store_address_line_1'],
                                    $store_info['store_address_line_2']
                                ],
                            'City' => null,
                            'StateOrProvinceCode' => $store_info['store_address_state'],
                            'PostalCode' => $store_info['store_address_zipcode'],
                            'CountryCode' => 'US',
                            'Residential' => 0
                        ]
                ],
            'Recipient' =>
                [
                    'Contact' =>
                        [
                            'PersonName' => null,
                            'PhoneNumber' => null
                        ],
                    'Address' =>
                        [
                            'StreetLines' =>
                                [
                                    $cart->getShippingAddress()->getLine1(),
                                    $cart->getShippingAddress()->getLine2()
                                ],
                            'City' => null,
                            'StateOrProvinceCode' => $cart->getShippingAddress()->getState(),
                            'PostalCode' => $cart->getShippingAddress()->getZipcode(),
                            'CountryCode' => 'US',
                            'Residential' => 0
                        ]
                ],
            'PreferredCurrency' => 'USD',
            'RequestedPackageLineItems' => [
                0 => [
                    'SequenceNumber' => 1,
                    'GroupPackageCount' => 1,
                    'InsuredValue' => [
                        'Currency' => 'USD',
                        'Amount' => 500
                    ],
                    'Weight' => [
                        'Value' => $cart->getTotalWeight(),
                        'Units' => 'LB'
                    ],
                    'Dimensions' => [
                        'Length' => 15,
                        'Width' => 15,
                        'Height' => 15,
                        'Units' => 'IN'
                    ]
                ]
            ]
        ];

        try
        {
            $response = $client->getRates($request);

            if ($response->HighestSeverity != 'FAILURE' && $response->HighestSeverity != 'ERROR')
            {
                $rateReply = $response->RateReplyDetails;

                // Get shipping methods
                if (count($rateReply) > 0)
                {
                    foreach ($rateReply as $ship_method)
                    {
                        $carrier_id = $ship_method->ServiceType;
                        $name = $ship_method->ServiceType;
                        $price = $ship_method->RatedShipmentDetails[0]->ShipmentRateDetail->TotalNetCharge->Amount;
                        $shipping_info[] = [
                            'shipping_method_id' => $carrier_id,
                            'name' => $name,
                            'price' => $price,
                            'carrier' => 'Fedex'
                        ];
                    }
                }
            }
            else
            {
                throw new \Exception("Error " . $response->Notifications->Code . " From Fedex: " . $response->Notifications->Message);
            }
        }
        catch (SoapFault $exception)
        {
            throw $exception;
        }

        return $shipping_info;
    }
}