<?php
/**
* The Shoplist super class definition.
*
* This super class represents models that have lists of skus, discounts and customer info
* @author Christopher Lee Reeves <ChrisReeves12@yahoo.com>
**/

namespace Library\Model\Shop;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\ManyToOne;
use Library\Model\AbstractModel;
use Library\Model\Relationship\ShopListDiscount;
use Library\Model\Shop\ShopList\Cart;
use Library\Model\Traits\StandardModelTrait;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\HasLifecycleCallbacks;
use Doctrine\ORM\Mapping\Table;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\DiscriminatorColumn;
use Doctrine\ORM\Mapping\DiscriminatorMap;
use Doctrine\ORM\Mapping\InheritanceType;
use Library\Model\User\Address;
use Library\Model\User\User;
use Library\Service\DB\EntityManagerSingleton;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Class ShopList
 * @package Library\Model\Shop
 */

/**
 * @Entity
 * @Table(name="shop_lists")
 * @HasLifecycleCallbacks
 * @InheritanceType("JOINED")
 * @DiscriminatorColumn(name="list_type", type="string")
 * @DiscriminatorMap({
 *   "shop_list" = "ShopList",
 *   "cart" = "Library\Model\Shop\ShopList\Cart",
 *   "query_list" = "Library\Model\Shop\ShopList\QueryList",
 *   "order" = "Library\Model\Shop\ShopList\Order"
 * })
 */
class ShopList extends AbstractModel
{

    use StandardModelTrait;

    /**
     * @ManyToOne(targetEntity="Library\Model\User\User")
     * @JoinColumn(name="user_id", referencedColumnName="id")
     * @var User
     */
    protected $user;

    /**
     * @ManyToOne(targetEntity="Library\Model\User\Address")
     * @JoinColumn(name="billing_address", referencedColumnName="id")
     * @var Address
     */
    protected $billing_address;

    /**
     * @ManyToOne(targetEntity="Library\Model\User\Address")
     * @JoinColumn(name="shipping_address", referencedColumnName="id")
     * @var Address
     */
    protected $shipping_address;

    /**
     * @ManyToOne(targetEntity="Library\Model\Shop\ShippingMethod")
     * @JoinColumn(name="shipping_method_id", referencedColumnName="id")
     * @var Address
     */
    protected $shipping_method;

    /**
     * @OneToMany(targetEntity="Library\Model\Relationship\ShopListDiscount", mappedBy="shop_list", cascade={"remove", "persist"})
     * @var ShopListDiscount[]
     */
    protected $shop_list_discounts;

    /**
     * @OneToMany(targetEntity="Library\Model\Shop\ShopListElement", mappedBy="shop_list", cascade={"remove", "persist"})
     * @var ShopListElement[]
     */
    protected $shop_list_elements;

    /**
     * @Column(name="ip_address", type="string", length=500, nullable=true)
     * @var string
     */
    protected $ip_address;

    /**
     * @Column(name="shipping_cost", type="decimal", scale=2, nullable=true)
     * @var float
     */
    protected $shipping_cost;

    /**
     * @Column(name="shipping_cost_override", type="decimal", scale=2, nullable=true)
     * @var float
     */
    protected $shipping_cost_override;

    /**
     * @ManyToOne(targetEntity="Library\Model\Shop\Tax")
     * @JoinColumn(name="sales_tax", referencedColumnName="id")
     * @var float
     */
    protected $sales_tax;

    /**
     * @Column(name="tax", type="decimal", scale=2, nullable=true)
     * @var float
     */
    protected $tax;

    /**
     * @Column(name="discount_amount", type="decimal", scale=2, nullable=true)
     * @var float
     */
    protected $discount_amount;

    /**
     * @Column(name="total_weight", type="decimal", scale=2, nullable=true)
     * @var float
     */
    protected $total_weight;

    /**
     * @Column(name="sub_total", type="decimal", scale=2, nullable=true)
     * @var float
     */
    protected $sub_total;

    /**
     * @Column(name="total", type="decimal", scale=2, nullable=true)
     * @var float
     */
    protected $total;

    /**
     * @Column(name="notes", type="text", nullable=true)
     * @var string
     */
    protected $notes;

    /**
     * @Column(name="store_credit", type="decimal", scale=2, nullable=true)
     * @var float
     */
    protected $store_credit;

    /**
     * @return float
     */
    public function getStoreCredit()
    {
        return $this->store_credit;
    }

    /**
     * @param float $store_credit
     */
    public function setStoreCredit($store_credit)
    {
        $this->store_credit = $store_credit;
    }

    /**
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param User $user
     */
    public function setUser($user)
    {
        $this->user = $user;
    }

    /**
     * @return Address
     */
    public function getBillingAddress()
    {
        return $this->billing_address;
    }

    /**
     * @return float
     */
    public function getSalesTax()
    {
        return $this->sales_tax;
    }

    /**
     * @param float $sales_tax
     */
    public function setSalesTax($sales_tax)
    {
        $this->sales_tax = $sales_tax;
    }

    /**
     * @param Address $billing_address
     */
    public function setBillingAddress($billing_address)
    {
        $this->billing_address = $billing_address;
    }

    /**
     * @return Address
     */
    public function getShippingAddress()
    {
        return $this->shipping_address;
    }

    /**
     * @return float
     */
    public function getTotalWeight()
    {
        return $this->total_weight;
    }

    /**
     * @return float
     */
    public function getTax()
    {
        return $this->tax;
    }

    /**
     * @param float $tax
     */
    public function setTax($tax)
    {
        $this->tax = $tax;
    }

    /**
     * @param float $total_weight
     */
    public function setTotalWeight($total_weight)
    {
        $this->total_weight = $total_weight;
    }

    /**
     * @param Address $shipping_address
     */
    public function setShippingAddress($shipping_address)
    {
        $this->shipping_address = $shipping_address;
    }

    /**
     * @return ShippingMethod
     */
    public function getShippingMethod()
    {
        return $this->shipping_method;
    }

    /**
     * @param ShippingMethod $shipping_method
     */
    public function setShippingMethod($shipping_method)
    {
        $this->shipping_method = $shipping_method;
    }

    /**
     * @return ShopListDiscount[]
     */
    public function getShopListDiscounts()
    {
        return $this->shop_list_discounts;
    }

    /**
     * @return float
     */
    public function getShippingCost()
    {
        return $this->shipping_cost;
    }

    /**
     * @param float $shipping_cost
     */
    public function setShippingCost($shipping_cost)
    {
        $this->shipping_cost = $shipping_cost;
    }

    /**
     * @return float
     */
    public function getShippingCostOverride()
    {
        return $this->shipping_cost_override;
    }

    /**
     * @param float $shipping_cost_override
     */
    public function setShippingCostOverride($shipping_cost_override)
    {
        $this->shipping_cost_override = $shipping_cost_override;
    }

    /**
     * Returns the shipping cost to use if there is an override
     * @return float
     */
    public function getCurrentShippingCost()
    {
        if (!is_null($this->shipping_cost_override))
            return $this->shipping_cost_override;
        else
            return $this->shipping_cost;
    }

    /**
     * @return float
     */
    public function getDiscountAmount()
    {
        return $this->discount_amount;
    }

    /**
     * @param float $discount_amount
     */
    public function setDiscountAmount($discount_amount)
    {
        $this->discount_amount = $discount_amount;
    }

    /**
     * @param ShopListDiscount[] $shop_list_discounts
     */
    public function setShopListDiscounts($shop_list_discounts)
    {
        $this->shop_list_discounts = $shop_list_discounts;
    }

    /**
     * Add a discount to the shop_list
     *
     * @param Discount $discount
     */
    public function addDiscount(Discount $discount)
    {
        $shop_list_discount = new ShopListDiscount();
        $shop_list_discount->setShopList($this);
        $shop_list_discount->setDiscount($discount);

        $this->shop_list_discounts[] = $shop_list_discount;
    }

    /**
     * Return an array of discounts associated with shop_list
     * @return Discount[]
     */
    public function getDiscounts()
    {
        if (!empty($this->shop_list_discounts))
        {
            $discounts = [];
            foreach ($this->shop_list_discounts as $relationship)
            {
                $discounts[] = $relationship->getDiscount();
            }
        }
        else
        {
            $discounts = [];
        }

        return $discounts;
    }

    /**
     * Returns an array of shop elements associated with the shop list
     *
     * @return ShopListElement[]
     */
    public function getShopListElements()
    {
        return $this->shop_list_elements;
    }

    /**
     * @return float
     */
    public function getSubTotal()
    {
        return $this->sub_total;
    }

    /**
     * @param float $sub_total
     */
    public function setSubTotal($sub_total)
    {
        $this->sub_total = $sub_total;
    }

    /**
     * @return float
     */
    public function getTotal()
    {
        return $this->total;
    }

    /**
     * @param float $total
     */
    public function setTotal($total)
    {
        $this->total = ($total < 0) ? 0 : $total;
    }

    /**
     * Sets the array of shop elements associated with the shop list
     *
     * @param ShopListElement[] $shop_list_elements
     */
    public function setShopListElements($shop_list_elements)
    {
        $this->shop_list_elements = $shop_list_elements;
    }

    /**
     * Adds a shop list element to the list
     *
     * @param ShopListElement $shop_list_element
     */
    public function addShopListElement(ShopListElement $shop_list_element)
    {
        $this->shop_list_elements[] = $shop_list_element;
    }

    /**
     * @return string
     */
    public function getIpAddress()
    {
        return $this->ip_address;
    }

    /**
     * @param string $ip_address
     */
    public function setIpAddress($ip_address)
    {
        $this->ip_address = $ip_address;
    }

    /**
     * Calculate total of list
     * @param ServiceLocatorInterface $service_manager
     */
    public function calculateTotals($service_manager)
    {
        $discount_service = $service_manager->get('discount');
        $em = EntityManagerSingleton::getInstance();
        $shipping_method_service = $service_manager->get('shippingMethod');
        $this->sub_total = 0;
        $this->total = 0;

        if (count($this->shop_list_elements) > 0)
        {
            if (!is_array($this->shop_list_elements))
                $shop_list_elements = $this->shop_list_elements->toArray();
            else
                $shop_list_elements = $this->shop_list_elements;

            foreach ($shop_list_elements as $shop_list_element)
            {
                $shop_list_element->calculateTotal();
                $this->sub_total += $shop_list_element->getTotal();
            }

            // Process realtime discounts, shipping total and store credit for cart
            if ($this instanceof Cart)
            {
                // Get updated shipping prices
                if (!is_null($this->getShippingMethod()) && count($this->shop_list_elements) > 0)
                {
                    $shipping_carriers_info = $shipping_method_service->get_methods($this);

                    if (!empty($shipping_carriers_info))
                    {
                        foreach ($shipping_carriers_info as $shipping_methods)
                        {
                            if (isset($shipping_methods) && count($shipping_methods) > 0)
                            {
                                foreach ($shipping_methods as $shipping_method)
                                {
                                    if ($this->getShippingMethod()->getCarrierId() == $shipping_method['shipping_method_id'])
                                    {
                                        $this->setShippingCost($shipping_method['price']);
                                    }
                                }
                            }
                        }
                    }
                }

                // Add necessary sales tax
                $this->tax = 0;

                if ($this->shipping_address instanceof Address)
                {
                    $tax = $em->getRepository('Library\Model\Shop\Tax')->findOneBy(['inactive' => false, 'state' => $this->shipping_address->getState()]);
                    $this->setSalesTax($tax);
                }

                // Process sales tax
                if ($this->sales_tax instanceof Tax)
                {
                    $discounted_sub_total = $this->sub_total - $this->discount_amount;
                    $this->tax = number_format($discounted_sub_total * ($this->sales_tax->getRate() * 0.01), 2, '.', '');
                }

                // Setting the store credit to null will force a recalculation of the store credit
                $this->setStoreCredit(null);

                // Calculate discounts
                $discount_service->processShopListDiscounts($this);
            }

            // Factor in store credit
            $pre_store_credit_total = (($this->sub_total - $this->discount_amount) + $this->getCurrentShippingCost()) + $this->tax;
            $this->calculateStoreCredit($pre_store_credit_total);

            $this->setTotal($pre_store_credit_total - $this->getStoreCredit());
        }
    }

    /**
     * Calculate total weight of list
     */
    public function calculateWeight()
    {
        $this->total_weight = 0;

        if (count($this->shop_list_elements) > 0)
        {
            foreach ($this->shop_list_elements as $shop_list_element)
            {
                $this->total_weight += ($shop_list_element->getWeight() * $shop_list_element->getQuantity());
            }
        }

        $this->total_weight = number_format($this->total_weight, 2, '.', '');
    }

    /**
     * Removes a shop list discount
     * @param ShopListDiscount $shop_list_discount_rel
     */
    public function removeShopListDiscount(ShopListDiscount $shop_list_discount_rel)
    {
        $this->shop_list_discounts->removeElement($shop_list_discount_rel);
    }

    /**
     * Calculates the store credit based on the total of the cart
     *
     * @param float $total
     * @return float
     */
    public function calculateStoreCredit($total = null)
    {
        if (empty($this->getStoreCredit()))
        {
            $user = $this->getUser();
            $user_store_credit = $user->getStoreCredit();
            $store_credit = 0;
            $grand_total = $total ?? $this->getTotal();

            // Make sure the store credit isn't more than the price
            if ($user instanceof User)
            {
                if ($user_store_credit > $grand_total)
                {
                    $store_credit = $grand_total;
                }
                else
                {
                    $store_credit = $user_store_credit;
                }
            }

            $this->setStoreCredit($store_credit);
        }
        else
        {
            $store_credit = $this->getStoreCredit();
        }

        return floatval(number_format($store_credit, 2));
    }

    /**
     * Automatically sets the date modified field
     */
    public function setDateModified()
    {
        $this->date_modified = new \DateTime();
    }

    /**
     * @return string
     */
    public function getNotes()
    {
        return $this->notes;
    }

    /**
     * @param string $notes
     */
    public function setNotes($notes)
    {
        $this->notes = $notes;
    }
}