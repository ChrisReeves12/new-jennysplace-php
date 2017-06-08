<?php
/**
* The CartService class definition.
*
* Various services for updating shopping carts
* @author Christopher Lee Reeves <ChrisReeves12@yahoo.com>
**/

namespace Library\Service;

use Library\Model\Shop\ShopList\Cart;
use Library\Model\User\Address;
use Library\Model\User\User;
use Library\Service\DB\EntityManagerSingleton;

/**
 * Class CartService
 * @package Library\Service
 */
class CartService extends AbstractService
{
    /**
     * Updates addresses on cart
     *
     * @param array $address_data
     *
     * @return array
     * @throws \Exception
     */
    public function updateAddresses($address_data)
    {
        $em = EntityManagerSingleton::getInstance();

        $user_service = $this->getServiceManager()->get('user');
        $user = $user_service->getIdentity();

        if (!($user instanceof User))
        {
            throw new \Exception("You must be logged in to change the addresses on your account.");
        }

        // Get shopping cart
        $saved_cart = $user->getSavedCart();
        if (!($saved_cart instanceof Cart))
        {
            throw new \Exception("The shopping cart cannot be found in the database.");
        }

        $billing_address_data = $address_data['billing_address_info'];
        $shipping_address_data = $address_data['shipping_address_info'];

        // Add billing and shipping address information
        $billing_address = $saved_cart->getBillingAddress();

        if (!($billing_address instanceof Address))
        {
            $billing_address = new Address();
            $saved_cart->setBillingAddress($billing_address);
        }

        $billing_address->setFirstName($user->getFirstName());
        $billing_address->setLastName($user->getLastName());
        $billing_address->setCompany($billing_address_data['company']);
        $billing_address->setLine1($billing_address_data['line_1']);
        $billing_address->setLine2($billing_address_data['line_2']);
        $billing_address->setEmail($billing_address_data['email']);
        $billing_address->setCity($billing_address_data['city']);
        $billing_address->setState($billing_address_data['state']);
        $billing_address->setZipcode($billing_address_data['zipcode']);
        $em->persist($billing_address);

        $shipping_address = $saved_cart->getShippingAddress();

        // Make sure a new shipping address is added when updating
        if (!($shipping_address instanceof Address))
        {
            $shipping_address = new Address();
            $saved_cart->setShippingAddress($shipping_address);
        }
        else
        {
            if ($shipping_address->getId() == $billing_address->getId() ||
                (($user->getShippingAddress() instanceof Address) &&
                    $shipping_address->getId() == $user->getShippingAddress()->getId()))
            {
                $shipping_address = new Address();
                $saved_cart->setShippingAddress($shipping_address);
            }
        }

        $shipping_address->setFirstName($user->getFirstName());
        $shipping_address->setLastName($user->getLastName());
        $shipping_address->setCompany($shipping_address_data['company']);
        $shipping_address->setLine1($shipping_address_data['line_1']);
        $shipping_address->setLine2($shipping_address_data['line_2']);
        $shipping_address->setEmail($shipping_address_data['email']);
        $shipping_address->setCity($shipping_address_data['city']);
        $shipping_address->setState($shipping_address_data['state']);
        $shipping_address->setZipcode($shipping_address_data['zipcode']);
        $em->persist($shipping_address);

        // Save billing address to user profile if the user has no billing address
        if (!($user->getBillingAddress() instanceof Address))
        {
            $user->setBillingAddress($billing_address);
        }

        $saved_cart->setDateModified();

        return $saved_cart;
    }
}