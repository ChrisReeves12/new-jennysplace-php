<?php
/**
* The CartController class definition.
*
* The frontend controller that houses functionality for the shopping cart
* @author Christopher Lee Reeves <ChrisReeves12@yahoo.com>
**/

namespace Frontend\Controller;

use Library\Controller\JPController;
use Library\Form\Cart\Checkout;
use Library\Form\User\InputFilter\FilterSpec;
use Library\Model\Media\Image;
use Library\Model\Page\Page;
use Library\Model\Product\Product;
use Library\Model\Product\Sku;
use Library\Model\Relationship\ShopListDiscount;
use Library\Model\Shop\Discount;
use Library\Model\Shop\ShippingMethod;
use Library\Model\Shop\ShopList;
use Library\Model\Shop\ShopList\Order;
use Library\Model\Shop\ShopList\Cart;
use Library\Model\Shop\ShopListElement;
use Library\Model\User\Address;
use Library\Model\User\User;
use Library\Service\DB\EntityManagerSingleton;
use Library\Service\Settings;
use Zend\InputFilter\Factory;
use Zend\View\Model\JsonModel;

/**
 * Class CartController
 * @package Frontend\Controller
 */
class CartController extends JPController
{
    
    protected $checkout_form;
    protected $order_response;

    /**
     * The main entry point for the shopping cart
     * @return array
     * @throws \Exception
     */
    public function indexAction()
    {
        // Set intermediate layout
        $main_layout = $this->layout()->getChildrenByCaptureTo('main_layout')[0];
        $main_layout->setTemplate('frontend/layout/shopping_layout');

        // Get entity manager
        $em = EntityManagerSingleton::getInstance();

        // Create page object
        $page = new Page();

        // Defaults
        $billing_address = $shipping_address = $shipping_carrier_info = $minimum_product_total = null;

        // Get store settings to popuplate page object
        $site_title = Settings::get('site_title');
        $page->setTitle($site_title);
        $this->setPage($page);

        // Check if user is logged in
        $user = $this->layout()->getVariable('user');
        if (!($user instanceof User))
        {
            // Redirect to login page
            return $this->redirect()->toUrl('/auth?whence=' . urlencode($_SERVER['REQUEST_URI']));
        }

        // Get user's saved shopping cart
        $saved_cart = $user->getSavedCart();
        $cart_items = [];

        // Get cart items
        if (($saved_cart instanceof Cart))
        {
            $cart_items = $saved_cart->getShopListElements()->toArray();

            // Add checkout form populate it
            $this->checkout_form = new Checkout();
            $address_update = false;

            $billing_address = $saved_cart->getBillingAddress();
            if (!($billing_address instanceof Address))
            {
                if ($user->getBillingAddress() instanceof Address)
                {
                    $billing_address = $user->getBillingAddress();
                    $saved_cart->setBillingAddress($billing_address);
                    $address_update = true;
                }
                else
                {
                    $billing_address = null;
                }
            }

            $shipping_address = $saved_cart->getShippingAddress();
            if (!($shipping_address instanceof Address))
            {
                $shipping_address = $user->getShippingAddress();
                if (!($shipping_address instanceof Address))
                {
                    $shipping_address = $billing_address ?? null;
                }
                $saved_cart->setShippingAddress($shipping_address);
                $address_update = true;
            }

            if ($address_update)
            {
                $em->flush();
            }

            // Set input filters up on form
            $this->checkout_form->setupFilterSpecs('billing_address', FilterSpec::getBillingAddressSpec());
            $this->checkout_form->setupFilterSpecs('shipping_address', FilterSpec::getShippingAddressSpec());

            // Handle incoming post
            $response = $this->handle_order_saving();
            if (!empty($response))
            {
                return new JsonModel($response);
            }

            // Popuplate forms
            if ($billing_address instanceof Address)
            {
                $this->checkout_form->get('billing_address')->get('company')->setValue($billing_address->getCompany());
                $this->checkout_form->get('billing_address')->get('first_name')->setValue($billing_address->getFirstName());
                $this->checkout_form->get('billing_address')->get('last_name')->setValue($billing_address->getLastName());
                $this->checkout_form->get('billing_address')->get('email')->setValue($billing_address->getEmail());
                $this->checkout_form->get('billing_address')->get('line_1')->setValue($billing_address->getLine1());
                $this->checkout_form->get('billing_address')->get('line_2')->setValue($billing_address->getLine2());
                $this->checkout_form->get('billing_address')->get('phone')->setValue($billing_address->getPhone());
                $this->checkout_form->get('billing_address')->get('city')->setValue($billing_address->getCity());
                $this->checkout_form->get('billing_address')->get('state')->setValue($billing_address->getState());
                $this->checkout_form->get('billing_address')->get('zipcode')->setValue($billing_address->getZipcode());
            }

            if ($shipping_address instanceof Address)
            {
                $this->checkout_form->get('shipping_address')->get('company')->setValue($shipping_address->getCompany());
                $this->checkout_form->get('shipping_address')->get('first_name')->setValue($shipping_address->getFirstName());
                $this->checkout_form->get('shipping_address')->get('last_name')->setValue($shipping_address->getLastName());
                $this->checkout_form->get('shipping_address')->get('email')->setValue($shipping_address->getEmail());
                $this->checkout_form->get('shipping_address')->get('line_1')->setValue($shipping_address->getLine1());
                $this->checkout_form->get('shipping_address')->get('line_2')->setValue($shipping_address->getLine2());
                $this->checkout_form->get('shipping_address')->get('phone')->setValue($shipping_address->getPhone());
                $this->checkout_form->get('shipping_address')->get('city')->setValue($shipping_address->getCity());
                $this->checkout_form->get('shipping_address')->get('state')->setValue($shipping_address->getState());
                $this->checkout_form->get('shipping_address')->get('zipcode')->setValue($shipping_address->getZipcode());
            }

            // Check for minimum product total
            $minimum_product_total = Settings::get('minimum_product_total');
            if (is_null($minimum_product_total) || is_nan($minimum_product_total))
            {
                $minimum_product_total = 0;
            }
        }

        // Add core javascript
        $this->getServiceLocator()->get('ViewRenderer')->headScript()->appendFile('/js/frontend/cart.js');

        return ['saved_cart' => $saved_cart, 'cart_items' => $cart_items, 'checkout_form' => $this->checkout_form,
            'billing_address' => $billing_address, 'shipping_address' => $shipping_address,
            'order_response' => $this->order_response, 'minimum_product_total' => $minimum_product_total];
    }

    /**
     * Gets shipping methods that are supported
     * @throws \Exception
     */
    public function shippingmethodsAction()
    {
        $user_service = $this->getServiceLocator()->get('user');
        $shipping_method_service = $this->getServiceLocator()->get('shippingMethod');
        $user = $user_service->getIdentity();
        $shipping_carriers_info = [];
        $current_method_id = $current_method_carrier = $current_method = null;

        if ($user instanceof User)
        {
            $saved_cart = $user->getSavedCart();
            if ($saved_cart instanceof Cart)
            {
                $shipping_carriers_info = $shipping_method_service->get_methods($saved_cart);

                // Get current method
                $current_method = $saved_cart->getShippingMethod();
                $current_method_id = $current_method_carrier = null;

                if ($current_method instanceof ShippingMethod)
                {
                    $current_method_id = $current_method->getCarrierId();
                    $current_method_carrier = $current_method->getCarrier();
                }
            }
        }

        return new JsonModel(['error' => false, 'shipping_methods' => $shipping_carriers_info, 'current_method' => ['id' => $current_method_id, 'carrier' => $current_method_carrier]]);
    }

    /**
     * Shows a reciept of an order, usually appears after an order has been placed
     * @return array
     * @throws \Exception
     */
    public function receiptAction()
    {
        $order_number = $this->params()->fromRoute('order_number');

        // Get current user
        $user = $this->layout()->getVariable('user');
        if (!($user instanceof User))
        {
            // Redirect to login page
            return $this->redirect()->toRoute('auth');
        }

        // Find order from id
        $em = EntityManagerSingleton::getInstance();
        $order = $em->getRepository('Library\Model\Shop\ShopList\Order')->findOneBy(['order_number' => $order_number]);
        if (!($order instanceof Order))
        {
            throw new \Exception("The order being queried cannot be found");
        }

        // Check if user is valid to view this receipt
        if ($order->getUser()->getId() != $user->getId())
        {
            // Redirect to the home page
            $this->redirect()->toRoute('home');
        }

        return ['user' => $user, 'order' => $order];
    }

    /**
     * Change addresses on billing and shipping in cart
     * @return array
     * @throws \Exception
     */
    public function updateaddressesAction()
    {
        $shipping_validations_errors = $billing_validations_errors = [];
        $shipping_method_service = $this->getServiceLocator()->get('shippingMethod');
        $cart_service = $this->getServiceLocator()->get('cart');
        $em = EntityManagerSingleton::getInstance();

        if ($this->getRequest()->isPost())
        {
            $data = $this->getRequest()->getPost()->toArray();

            // Create a form to filter results
            $filterFactory = new Factory();
            $filter_spec = FilterSpec::getBillingAddressSpec();
            $filter = $filterFactory->createInputFilter($filter_spec);

            $filter->setData($data['billing_address']);

            if ($filter->isValid())
            {
                $billing_address_data = $filter->getValues();
            }
            else
            {
                $billing_validations_errors = $filter->getMessages();

                // Correct input names
                foreach ($billing_validations_errors as $key => $validation_error)
                {
                    $new_key = "billing_address[{$key}]";
                    $billing_validations_errors[$new_key] = $validation_error;
                    unset($billing_validations_errors[$key]);
                }
            }

            // Create a form to filter results
            $filterFactory = new Factory();
            $filter_spec = FilterSpec::getBillingAddressSpec();
            $filter = $filterFactory->createInputFilter($filter_spec);

            $filter->setData($data['shipping_address']);
            if ($filter->isValid())
            {
                $shipping_address_data = $filter->getValues();

            }
            else
            {
                $shipping_validations_errors = $filter->getMessages();

                // Correct input names
                foreach ($shipping_validations_errors as $key => $validation_error)
                {
                    $new_key = "shipping_address[{$key}]";
                    $shipping_validations_errors[$new_key] = $validation_error;
                    unset($shipping_validations_errors[$key]);
                }
            }
        }

        // Report errors
        if (!empty($billing_validations_errors) || !empty($shipping_validations_errors))
        {
            $validation_errors = array_merge($billing_validations_errors, $shipping_validations_errors);
            return new JsonModel(['error' => false, 'validation_errors' => $validation_errors]);
        }
        else
        {
            // If no errors, save data
            $saved_cart = $cart_service->updateAddresses(['billing_address_info' => $billing_address_data, 'shipping_address_info' => $shipping_address_data]);
            $em->flush();
        }

        // Get shipping methods
        $shipping_carriers_info = $shipping_method_service->get_methods($saved_cart);
        $current_method_id = $current_method_carrier = null;
        $current_method = $saved_cart->getShippingMethod();
        if ($current_method instanceof ShippingMethod)
        {
            $current_method_id = $current_method->getCarrierId();
            $current_method_carrier = $current_method->getCarrier();
        }

        return new JsonModel(['error' => false,
            'sub_total' => $saved_cart->getSubTotal(),
            'shipping_methods' => $shipping_carriers_info,
            'shipping_cost' => $saved_cart->getCurrentShippingCost(),
            'current_method' => ['id' => $current_method_id, 'carrier' => $current_method_carrier],
            'total' => $saved_cart->getTotal()]);
    }

    /**
     * Adds an item to the shopping cart
     */
    public function addAction()
    {
        $user_service = $this->getServiceLocator()->get('user');

        // Get post
        if ($this->getRequest()->isPost())
        {
            $data = $this->getRequest()->getPost()->toArray();
            $options = isset($data['options']) ? $data['options'] : [];

            // Get current user
            $user = $this->layout()->getVariable('user');
            if (!($user instanceof User))
                throw new \Exception("You must be logged in to add items to your shopping cart.");

            $em = EntityManagerSingleton::getInstance();

            // Get product
            /** @var Product $product */
            $product = $em->getRepository('Library\Model\Product\Product')->findOneById($data['product_id']);

            // Find sku by options if options are there
            if (!empty($options))
            {
                // Get sku from options
                $sku = $em->getRepository('Library\Model\Product\Sku')->findOneByOptions($product, $options);
            }
            else
            {
                // Get default sku if product has no options
                $sku = $product->getSkus()[0];
            }

            if (!($sku instanceof Sku))
            {
                // Create error message to show to user
                if (count($options) > 0)
                {
                    $option_ids = [];
                    foreach ($options as $option_id => $value_id)
                    {
                        $option_ids[] = $option_id;
                    }
                    $options = $em->getRepository('Library\Model\Product\Option')->findBy(['id' => $option_ids]);
                    $option_message = "";

                    if (count($options) == 1) $option_message = $options[0]->getName(); elseif (count($options) == 2) $option_message = $options[0]->getName() . ' and ' . $options[1]->getName();
                    elseif (count($options) == 3)
                    {
                        $option_message = $options[0]->getName() . ', ' . $options[1]->getName() . ', and ' . $options[2]->getName();
                    } elseif (count($options) > 3)
                    {
                        $option_message = 'option values';
                    }
                    $option_message = strtolower($option_message);

                    // Sku doesn't exist and may be out of stock
                    return new JsonModel(['error' => true, 'message' => "This product in the selected {$option_message} is currently out of stock. Please choose another selection."]);
                }
                else
                {
                    // Product's default sku is missing
                    return new JsonModel(['error' => true, 'message' => "This product is currently out of stock."]);
                }
            }
            else
            {
                // Check if the sku is in stock
                if ($sku->getIsDefault())
                {
                    $stock_status = $product->getStatus();
                }
                else
                {
                    $stock_status = $sku->getStatus();
                }
            }

            // Show customer message if item is out of stock
            if ($stock_status->getId() != 1)
            {
                switch ($stock_status->getId())
                {
                    case 2:
                        $message = "This item is currently out of stock.";
                        break;

                    case 3:
                        $message = "This item is currently disabled";
                        break;

                    case 5:
                        $message = "This item is currently on backorder and is not available at this time.";
                        break;
                    default:
                        $message = "This item is currently unavailable";
                        break;
                }

                return new JsonModel(['error' => true, 'message' => $message]);
            }
            else
            {
                // Load the cart and add the sku
                $cart = $user->getSavedCart();
                if (!($cart instanceof Cart))
                {
                    $cart = new Cart();
                    $cart->setDateModified();
                }

                // Add new line item
                $cart_element = new ShopListElement();
                $cart_element->setShopList($cart);
                $cart_element->convertSkuToElement($sku, $data['qty']);

                // Put line item in cart for new carts so that total is calculated correctly the first time
                if (is_null($cart->getId()))
                {
                    $cart->addShopListElement($cart_element);
                }

                $em->persist($cart_element);

                // Set cart attributes
                $cart->setUser($user);
                $cart->setIpAddress($user_service->getClientIp());

                if (is_null($cart->getShippingCost()))
                    $cart->setShippingCost(0);

                if (is_null($cart->getTax()))
                    $cart->setTax(0);

                if (is_null($cart->getDiscountAmount()))
                    $cart->setDiscountAmount(0);

                // Set user's cart
                $user->setSavedCart($cart);

                // Update the shop list to trigger the event listener to update the total
                $cart->setDateModified();

                $em->persist($cart);
                $em->flush();

                // Find correct product image to use
                if ($product->getDefaultImage() instanceof Image)
                    $product_image_url = Settings::get('image_path') . '/product_images/' . $product->getDefaultImage()->getUrl();
                else
                    $product_image_url = '/img/layout_images/no_photo.jpg';

                // Get product information to send to javascript
                $product_info = [
                    'name' => $product->getName(),
                    'quantity' => $data['qty'],
                    'href' => '/product/' . $product->getPage()->getUrlHandle(),
                    'image' => $product_image_url
                ];

                // Send success
                return new JsonModel(['error' => false, 'product' => $product_info, 'total' => number_format($cart->getSubTotal(), 2), 'count' => count($cart->getShopListElements())]);
            }
        }

        return null;
    }

    /**
     * Removes item from the shopping cart
     */
    public function removeAction()
    {
        $user_service = $this->getServiceLocator()->get('user');

        // Get post
        if ($this->getRequest()->isPost())
        {
            $data = $this->getRequest()->getPost()->toArray();

            // Get current user
            $user = $user_service->getIdentity();
            if (!($user instanceof User))
            {
                throw new \Exception("You must be logged in to remove items from your cart.");
            }

            // Get the user cart
            $saved_cart = $user->getSavedCart();
            if (!($saved_cart instanceof Cart))
            {
                throw new \Exception("The shopping cart has been removed from the database.");
            }

            // Remove shop list element
            $em = EntityManagerSingleton::getInstance();
            $cart_item = $em->getRepository('Library\Model\Shop\ShopListElement')->findOneById($data['element_id']);
            $cart_items = $saved_cart->getShopListElements();

            if ($cart_item instanceof ShopListElement)
            {
                $cart_items->removeElement($cart_item);
                $em->remove($cart_item);
            }

            // Update the shop list to trigger the event listener to update the total
            $saved_cart->setDateModified();

            $em->flush();

            return new JsonModel(['error' => false]);
        }

        return null;
    }

    /**
     * Updates a line item in the cart
     */
    public function updateAction()
    {
        $user_service = $this->getServiceLocator()->get('user');

        // Get post
        if ($this->getRequest()->isPost())
        {
            $data = $this->getRequest()->getPost()->toArray();

            // Get current user
            $user = $user_service->getIdentity();
            if (!($user instanceof User))
            {
                throw new \Exception("You must be logged in to update your shopping cart.");
            }

            // Get the user cart
            $saved_cart = $user->getSavedCart();
            if (!($saved_cart instanceof Cart))
            {
                throw new \Exception("The shopping cart has been removed from the database.");
            }

            // Remove shop list element
            $em = EntityManagerSingleton::getInstance();
            $cart_item = $em->getRepository('Library\Model\Shop\ShopListElement')->findOneById($data['element_id']);
            $cart_item->setQuantity($data['quantity']);

            // Update the shop list to trigger the event listener to update the total
            $saved_cart->setDateModified();

            $em->flush();

            return new JsonModel(['error' => false]);
        }

        return null;
    }

    /**
     * Changes the shipping method of the cart
     */
    public function shippingmethodAction()
    {
        $user_service = $this->getServiceLocator()->get('user');

        // Get post
        if ($this->getRequest()->isPost())
        {
            $data = $this->getRequest()->getPost()->toArray();

            // Get current user
            $user = $user_service->getIdentity();
            if (!($user instanceof User))
            {
                throw new \Exception("You must be logged in to modify your order.");
            }

            // Get the user cart
            $saved_cart = $user->getSavedCart();
            if (!($saved_cart instanceof Cart))
            {
                throw new \Exception("The shopping cart has been removed from the database.");
            }

            // Save information to cart
            $em = EntityManagerSingleton::getInstance();
            $saved_cart->setShippingCost($data['shipping_price']);
            $shipping_method = $em->getRepository('Library\Model\Shop\ShippingMethod')->findOneBy(['carrier_id' => $data['shipping_method'], 'carrier' => $data['carrier']]);
            if (!($shipping_method instanceof ShippingMethod))
            {
                throw new \Exception("The shipping method being selected is invalid.");
            }

            $saved_cart->setShippingMethod($shipping_method);

            $em->flush();

            return new JsonModel(['error' => false, 'total' => number_format($saved_cart->getTotal(), 2), 'store_credit' => $saved_cart->calculateStoreCredit(), 'shipping_cost' => number_format($saved_cart->getCurrentShippingCost(), 2, '.', '')]);
        }

        return null;
    }

    /**
     * Add discount code
     * @return array
     * @throws \Exception
     */
    public function adddiscountAction()
    {
        $user_service = $this->getServiceLocator()->get('user');

        // Get post
        if ($this->getRequest()->isPost())
        {
            $data = $this->getRequest()->getPost()->toArray();

            // Get current user
            $user = $user_service->getIdentity();
            if (!($user instanceof User))
            {
                throw new \Exception("You must be logged in to modify your order.");
            }

            // Get the user cart
            $saved_cart = $user->getSavedCart();
            if (!($saved_cart instanceof Cart))
            {
                throw new \Exception("The shopping cart has been removed from the database.");
            }

            // Check if there are addresses
            if (is_null($saved_cart->getBillingAddress()) || is_null($saved_cart->getShippingAddress()))
            {
                throw new \Exception("Please provide your Billing and Shipping Addresses before applying a discount code.");
            }

            // Find discount code based on input
            $em = EntityManagerSingleton::getInstance();
            $discount = $em->getRepository('Library\Model\Shop\Discount')->findOneByCode($data['discount_code']);

            // Send error if discount is not found
            if (!($discount instanceof Discount))
            {
                return new JsonModel(['error' => true, 'message' => 'The discount code is not valid']);
            }

            // Check if the discount code isn't already added and that we are allowed to have multiple discounts
            $discount_rels = $saved_cart->getShopListDiscounts()->toArray();
            $bypass_discount_adding = false;

            if (!empty($discount_rels))
            {
                if (Settings::get('multi_discount') == '0')
                {
                    $bypass_discount_adding = true;
                }
                else
                {
                    foreach ($discount_rels as $discount_rel)
                    {
                        if ($discount->getId() == $discount_rel->getDiscount()->getId()) $bypass_discount_adding = true;
                    }
                }
            }

            // Add the discount to the cart
            if (false === $bypass_discount_adding)
            {
                $saved_cart->addDiscount($discount);

                // Update cart
                $saved_cart->setDateModified();
            }

            $em->flush();

            return new JsonModel(['error' => false]);
        }

        return [];
    }

    /**
     * Removes discount from saved cart
     * @return array
     * @throws \Exception
     */
    public function removediscountAction()
    {
        $user_service = $this->getServiceLocator()->get('user');

        // Get post
        if ($this->getRequest()->isPost())
        {
            $data = $this->getRequest()->getPost()->toArray();

            // Get current user
            $user = $user_service->getIdentity();
            if (!($user instanceof User))
            {
                throw new \Exception("You must be logged in to modify your order.");
            }

            // Get the user cart
            $saved_cart = $user->getSavedCart();
            if (!($saved_cart instanceof Cart))
            {
                throw new \Exception("The shopping cart has been removed from the database.");
            }

            // Delete the relationship
            $em = EntityManagerSingleton::getInstance();
            $discount_rel = $em->getRepository('Library\Model\Relationship\ShopListDiscount')->findOneById($data['discount_rel_id']);
            if (!($discount_rel instanceof ShopListDiscount))
            {
                return new JsonModel(['error' => false]);
            }

            $saved_cart->removeShopListDiscount($discount_rel);
            $em->remove($discount_rel);

            // Update cart
            $saved_cart->setDateModified();

            $em->flush();

            return new JsonModel(['error' => false]);
        }

        return [];
    }

    /**
     * Handle saving orders
     * @return array
     * @throws \Exception
     */
    protected function handle_order_saving()
    {
        $order_service = $this->getServiceLocator()->get('order');
        $user_service = $this->getServiceLocator()->get('user');

        // Get post
        if ($this->getRequest()->isPost())
        {
            // Get data from post
            $data = $this->getRequest()->getPost()->toArray();
            $task = $data['task'];
            unset($data['task']);

            // Handle different tasks
            switch ($task)
            {
                case 'place_order':

                    // Get current user
                    $user = $user_service->getIdentity();
                    if (!($user instanceof User))
                    {
                        throw new \Exception("You must be logged in to place orders.");
                    }

                    // Get the user cart
                    $saved_cart = $user->getSavedCart();
                    if (!($saved_cart instanceof Cart))
                    {
                        throw new \Exception("The shopping cart has been removed from the database.");
                    }

                    // Check if there is a billing address
                    $billing_address = $saved_cart->getBillingAddress();
                    if (!$billing_address instanceof Address)
                    {
                        throw new \Exception("Please provide your Billing and Shipping Addresses before applying a discount code.");
                    }

                    // Check if there is a shipping method selected
                    $shipping_method = $saved_cart->getShippingMethod();
                    if (!($shipping_method instanceof ShippingMethod))
                    {
                        throw new \Exception("Please select a shipping method for your order.");
                    }

                    // Validate address forms
                    $this->checkout_form->setData($data);
                    if ($this->checkout_form->isValid())
                    {
                        $new_data = $this->checkout_form->getData();
                        $this->order_response = $order_service->save($new_data, null);
                        $response = $this->finalize_order_and_redirect($user, $saved_cart);
                        if (!empty($response))
                        {
                            return $response;
                        }
                    }

                    break;
            }
        }

        return null;
    }

    /**
     * Processes returning payment gateway requests
     *
     * @return void|\Zend\Http\Response
     * @throws \Exception
     */
    public function returnAction()
    {
        $order_service = $this->getServiceLocator()->get('order');
        $user_service = $this->getServiceLocator()->get('user');

        // Get current user
        $user = $user_service->getIdentity();
        if (!($user instanceof User))
        {
            return $this->redirect()->toRoute('auth');
        }

        // Get the user cart
        $saved_cart = $user->getSavedCart();
        if (!($saved_cart instanceof Cart))
        {
            throw new \Exception("The shopping cart has been removed from the database.");
        }

        $this->order_response = $order_service->save(null, null);
        $response = $this->finalize_order_and_redirect($user, $saved_cart);
        if (!empty($response))
        {
            return new JsonModel($response);
        }

        return [];
    }

    /**
     * Finalizes saved orders and sends user to receipt page
     *
     * @param $user
     * @param $saved_cart
     *
     * @return array
     * @throws \Exception
     */
    public function finalize_order_and_redirect($user, $saved_cart)
    {
        if ($this->order_response instanceof Order)
        {
            // Get entity manager
            $em = EntityManagerSingleton::getInstance();

            // Remove cart
            $user->setSavedCart(null);
            $em->remove($saved_cart);

            // Save order
            $em->flush();

            // Send user to reciept page
            if (!empty($_POST) && ($_POST['pay_info']['pay_method'] == 'Credit/Debit' || $_POST['pay_info']['pay_method'] == 'Store Credit/Voucher'))
            {
                return ['error' => false, 'order_number' => $this->order_response->getOrderNumber()];
            }
            else
            {
                $this->redirect()->toUrl('/shopping-cart/receipt/' . $this->order_response->getOrderNumber());
            }
        }
        elseif (is_array($this->order_response))
        {
            // Check for errors
            if (isset($this->order_response['success']) && $this->order_response['success'] == false)
            {
                throw new \Exception($this->order_response['response_text']);
            }
        }

        return null;
    }
}