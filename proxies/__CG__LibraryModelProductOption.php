<?php

namespace DoctrineProxies\__CG__\Library\Model\Product;

/**
 * DO NOT EDIT THIS FILE - IT WAS CREATED BY DOCTRINE'S PROXY GENERATOR
 */
class Option extends \Library\Model\Product\Option implements \Doctrine\ORM\Proxy\Proxy
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
            return array('__isInitialized__', 'name', 'option_option_values', 'id', 'date_created', 'date_modified');
        }

        return array('__isInitialized__', 'name', 'option_option_values', 'id', 'date_created', 'date_modified');
    }

    /**
     * 
     */
    public function __wakeup()
    {
        if ( ! $this->__isInitialized__) {
            $this->__initializer__ = function (Option $proxy) {
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
    public function getOptionOptionValues()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getOptionOptionValues', array());

        return parent::getOptionOptionValues();
    }

    /**
     * {@inheritDoc}
     */
    public function setOptionOptionValues($option_option_values)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setOptionOptionValues', array($option_option_values));

        return parent::setOptionOptionValues($option_option_values);
    }

    /**
     * {@inheritDoc}
     */
    public function addOptionValue(\Library\Model\Product\OptionValue $option_value)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'addOptionValue', array($option_value));

        return parent::addOptionValue($option_value);
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
    public function setDateModified($date_modified = NULL)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setDateModified', array($date_modified));

        return parent::setDateModified($date_modified);
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