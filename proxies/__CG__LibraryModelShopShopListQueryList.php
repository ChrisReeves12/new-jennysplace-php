<?php

namespace DoctrineProxies\__CG__\Library\Model\Shop\ShopList;

/**
 * DO NOT EDIT THIS FILE - IT WAS CREATED BY DOCTRINE'S PROXY GENERATOR
 */
class QueryList extends \Library\Model\Shop\ShopList\QueryList implements \Doctrine\ORM\Proxy\Proxy
{
    /**
     * @var \Closure the callback responsible for loading properties in the proxy object. This callback is called with
     *      three parameters, being respectively the proxy object to be initialized, the method that triggered the
     *      initialization process and an array of ordered parameters that were passed to that method.
     *
     * @see \Doctrine\Common\Persistence\Proxy::__setInitializer
     */
    public $__initializer__;

    /**
     * @var \Closure the callback responsible of loading properties that need to be copied in the cloned object
     *
     * @see \Doctrine\Common\Persistence\Proxy::__setCloner
     */
    public $__cloner__;

    /**
     * @var boolean flag indicating if this object was already initialized
     *
     * @see \Doctrine\Common\Persistence\Proxy::__isInitialized
     */
    public $__isInitialized__ = false;

    /**
     * @var array properties to be lazy loaded, with keys being the property
     *            names and values being their default values
     *
     * @see \Doctrine\Common\Persistence\Proxy::__getLazyProperties
     */
    public static $lazyPropertiesDefaults = array();



    /**
     * @param \Closure $initializer
     * @param \Closure $cloner
     */
    public function __construct($initializer = null, $cloner = null)
    {

        $this->__initializer__ = $initializer;
        $this->__cloner__      = $cloner;
    }







    /**
     * 
     * @return array
     */
    public function __sleep()
    {
        if ($this->__isInitialized__) {
            return array('__isInitialized__', 'query', 'name', 'user', 'billing_address', 'shipping_address', 'shipping_method', 'shop_list_discounts', 'shop_list_elements', 'ip_address', 'shipping_cost', 'shipping_cost_override', 'sales_tax', 'tax', 'discount_amount', 'total_weight', 'sub_total', 'total', 'notes', 'store_credit', 'id', 'date_created', 'date_modified');
        }

        return array('__isInitialized__', 'query', 'name', 'user', 'billing_address', 'shipping_address', 'shipping_method', 'shop_list_discounts', 'shop_list_elements', 'ip_address', 'shipping_cost', 'shipping_cost_override', 'sales_tax', 'tax', 'discount_amount', 'total_weight', 'sub_total', 'total', 'notes', 'store_credit', 'id', 'date_created', 'date_modified');
    }

    /**
     * 
     */
    public function __wakeup()
    {
        if ( ! $this->__isInitialized__) {
            $this->__initializer__ = function (QueryList $proxy) {
                $proxy->__setInitializer(null);
                $proxy->__setCloner(null);

                $existingProperties = get_object_vars($proxy);

                foreach ($proxy->__getLazyProperties() as $property => $defaultValue) {
                    if ( ! array_key_exists($property, $existingProperties)) {
                        $proxy->$property = $defaultValue;
                    }
                }
            };

        }
    }

    /**
     * 
     */
    public function __clone()
    {
        $this->__cloner__ && $this->__cloner__->__invoke($this, '__clone', array());
    }

    /**
     * Forces initialization of the proxy
     */
    public function __load()
    {
        $this->__initializer__ && $this->__initializer__->__invoke($this, '__load', array());
    }

    /**
     * {@inheritDoc}
     * @internal generated method: use only when explicitly handling proxy specific loading logic
     */
    public function __isInitialized()
    {
        return $this->__isInitialized__;
    }

    /**
     * {@inheritDoc}
     * @internal generated method: use only when explicitly handling proxy specific loading logic
     */
    public function __setInitialized($initialized)
    {
        $this->__isInitialized__ = $initialized;
    }

    /**
     * {@inheritDoc}
     * @internal generated method: use only when explicitly handling proxy specific loading logic
     */
    public function __setInitializer(\Closure $initializer = null)
    {
        $this->__initializer__ = $initializer;
    }

    /**
     * {@inheritDoc}
     * @internal generated method: use only when explicitly handling proxy specific loading logic
     */
    public function __getInitializer()
    {
        return $this->__initializer__;
    }

    /**
     * {@inheritDoc}
     * @internal generated method: use only when explicitly handling proxy specific loading logic
     */
    public function __setCloner(\Closure $cloner = null)
    {
        $this->__cloner__ = $cloner;
    }

    /**
     * {@inheritDoc}
     * @internal generated method: use only when explicitly handling proxy specific cloning logic
     */
    public function __getCloner()
    {
        return $this->__cloner__;
    }

    /**
     * {@inheritDoc}
     * @internal generated method: use only when explicitly handling proxy specific loading logic
     * @static
     */
    public function __getLazyProperties()
    {
        return self::$lazyPropertiesDefaults;
    }

    
    /**
     * {@inheritDoc}
     */
    public function getQuery()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getQuery', array());

        return parent::getQuery();
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getName', array());

        return parent::getName();
    }

    /**
     * {@inheritDoc}
     */
    public function setName($name)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setName', array($name));

        return parent::setName($name);
    }

    /**
     * {@inheritDoc}
     */
    public function setQuery($query)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setQuery', array($query));

        return parent::setQuery($query);
    }

    /**
     * {@inheritDoc}
     */
    public function calculateTotals()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'calculateTotals', array());

        return parent::calculateTotals();
    }

    /**
     * {@inheritDoc}
     */
    public function calculateWeight()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'calculateWeight', array());

        return parent::calculateWeight();
    }

    /**
     * {@inheritDoc}
     */
    public function getStoreCredit()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getStoreCredit', array());

        return parent::getStoreCredit();
    }

    /**
     * {@inheritDoc}
     */
    public function setStoreCredit($store_credit)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setStoreCredit', array($store_credit));

        return parent::setStoreCredit($store_credit);
    }

    /**
     * {@inheritDoc}
     */
    public function getUser()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getUser', array());

        return parent::getUser();
    }

    /**
     * {@inheritDoc}
     */
    public function setUser($user)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setUser', array($user));

        return parent::setUser($user);
    }

    /**
     * {@inheritDoc}
     */
    public function getBillingAddress()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getBillingAddress', array());

        return parent::getBillingAddress();
    }

    /**
     * {@inheritDoc}
     */
    public function getSalesTax()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getSalesTax', array());

        return parent::getSalesTax();
    }

    /**
     * {@inheritDoc}
     */
    public function setSalesTax($sales_tax)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setSalesTax', array($sales_tax));

        return parent::setSalesTax($sales_tax);
    }

    /**
     * {@inheritDoc}
     */
    public function setBillingAddress($billing_address)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setBillingAddress', array($billing_address));

        return parent::setBillingAddress($billing_address);
    }

    /**
     * {@inheritDoc}
     */
    public function getShippingAddress()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getShippingAddress', array());

        return parent::getShippingAddress();
    }

    /**
     * {@inheritDoc}
     */
    public function getTotalWeight()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getTotalWeight', array());

        return parent::getTotalWeight();
    }

    /**
     * {@inheritDoc}
     */
    public function getTax()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getTax', array());

        return parent::getTax();
    }

    /**
     * {@inheritDoc}
     */
    public function setTax($tax)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setTax', array($tax));

        return parent::setTax($tax);
    }

    /**
     * {@inheritDoc}
     */
    public function setTotalWeight($total_weight)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setTotalWeight', array($total_weight));

        return parent::setTotalWeight($total_weight);
    }

    /**
     * {@inheritDoc}
     */
    public function setShippingAddress($shipping_address)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setShippingAddress', array($shipping_address));

        return parent::setShippingAddress($shipping_address);
    }

    /**
     * {@inheritDoc}
     */
    public function getShippingMethod()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getShippingMethod', array());

        return parent::getShippingMethod();
    }

    /**
     * {@inheritDoc}
     */
    public function setShippingMethod($shipping_method)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setShippingMethod', array($shipping_method));

        return parent::setShippingMethod($shipping_method);
    }

    /**
     * {@inheritDoc}
     */
    public function getShopListDiscounts()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getShopListDiscounts', array());

        return parent::getShopListDiscounts();
    }

    /**
     * {@inheritDoc}
     */
    public function getShippingCost()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getShippingCost', array());

        return parent::getShippingCost();
    }

    /**
     * {@inheritDoc}
     */
    public function setShippingCost($shipping_cost)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setShippingCost', array($shipping_cost));

        return parent::setShippingCost($shipping_cost);
    }

    /**
     * {@inheritDoc}
     */
    public function getShippingCostOverride()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getShippingCostOverride', array());

        return parent::getShippingCostOverride();
    }

    /**
     * {@inheritDoc}
     */
    public function setShippingCostOverride($shipping_cost_override)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setShippingCostOverride', array($shipping_cost_override));

        return parent::setShippingCostOverride($shipping_cost_override);
    }

    /**
     * {@inheritDoc}
     */
    public function getCurrentShippingCost()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getCurrentShippingCost', array());

        return parent::getCurrentShippingCost();
    }

    /**
     * {@inheritDoc}
     */
    public function getDiscountAmount()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getDiscountAmount', array());

        return parent::getDiscountAmount();
    }

    /**
     * {@inheritDoc}
     */
    public function setDiscountAmount($discount_amount)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setDiscountAmount', array($discount_amount));

        return parent::setDiscountAmount($discount_amount);
    }

    /**
     * {@inheritDoc}
     */
    public function setShopListDiscounts($shop_list_discounts)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setShopListDiscounts', array($shop_list_discounts));

        return parent::setShopListDiscounts($shop_list_discounts);
    }

    /**
     * {@inheritDoc}
     */
    public function addDiscount(\Library\Model\Shop\Discount $discount)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'addDiscount', array($discount));

        return parent::addDiscount($discount);
    }

    /**
     * {@inheritDoc}
     */
    public function getDiscounts()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getDiscounts', array());

        return parent::getDiscounts();
    }

    /**
     * {@inheritDoc}
     */
    public function getShopListElements()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getShopListElements', array());

        return parent::getShopListElements();
    }

    /**
     * {@inheritDoc}
     */
    public function getSubTotal()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getSubTotal', array());

        return parent::getSubTotal();
    }

    /**
     * {@inheritDoc}
     */
    public function setSubTotal($sub_total)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setSubTotal', array($sub_total));

        return parent::setSubTotal($sub_total);
    }

    /**
     * {@inheritDoc}
     */
    public function getTotal()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getTotal', array());

        return parent::getTotal();
    }

    /**
     * {@inheritDoc}
     */
    public function setTotal($total)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setTotal', array($total));

        return parent::setTotal($total);
    }

    /**
     * {@inheritDoc}
     */
    public function setShopListElements($shop_list_elements)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setShopListElements', array($shop_list_elements));

        return parent::setShopListElements($shop_list_elements);
    }

    /**
     * {@inheritDoc}
     */
    public function addShopListElement(\Library\Model\Shop\ShopListElement $shop_list_element)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'addShopListElement', array($shop_list_element));

        return parent::addShopListElement($shop_list_element);
    }

    /**
     * {@inheritDoc}
     */
    public function getIpAddress()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getIpAddress', array());

        return parent::getIpAddress();
    }

    /**
     * {@inheritDoc}
     */
    public function setIpAddress($ip_address)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setIpAddress', array($ip_address));

        return parent::setIpAddress($ip_address);
    }

    /**
     * {@inheritDoc}
     */
    public function removeShopListDiscount(\Library\Model\Relationship\ShopListDiscount $shop_list_discount_rel)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'removeShopListDiscount', array($shop_list_discount_rel));

        return parent::removeShopListDiscount($shop_list_discount_rel);
    }

    /**
     * {@inheritDoc}
     */
    public function calculateStoreCredit($total = NULL)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'calculateStoreCredit', array($total));

        return parent::calculateStoreCredit($total);
    }

    /**
     * {@inheritDoc}
     */
    public function setDateModified()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setDateModified', array());

        return parent::setDateModified();
    }

    /**
     * {@inheritDoc}
     */
    public function getNotes()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getNotes', array());

        return parent::getNotes();
    }

    /**
     * {@inheritDoc}
     */
    public function setNotes($notes)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setNotes', array($notes));

        return parent::setNotes($notes);
    }

    /**
     * {@inheritDoc}
     */
    public function setData($data_array)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setData', array($data_array));

        return parent::setData($data_array);
    }

    /**
     * {@inheritDoc}
     */
    public function toArray($ignore_attribute = NULL)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'toArray', array($ignore_attribute));

        return parent::toArray($ignore_attribute);
    }

    /**
     * {@inheritDoc}
     */
    public function showDisplayName()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'showDisplayName', array());

        return parent::showDisplayName();
    }

    /**
     * {@inheritDoc}
     */
    public function getId()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getId', array());

        return parent::getId();
    }

    /**
     * {@inheritDoc}
     */
    public function setId($id)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setId', array($id));

        return parent::setId($id);
    }

    /**
     * {@inheritDoc}
     */
    public function setDateCreated($date_created = NULL)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setDateCreated', array($date_created));

        return parent::setDateCreated($date_created);
    }

    /**
     * {@inheritDoc}
     */
    public function getDateCreated()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getDateCreated', array());

        return parent::getDateCreated();
    }

    /**
     * {@inheritDoc}
     */
    public function getDateModified()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getDateModified', array());

        return parent::getDateModified();
    }

}